<?php

namespace App\Services;

use App\app\traits\CheckRequestStatus;
use App\Events\FeeWaiverHandled;
use App\Events\FeeWaiverRequested;
use App\Models\FeeWaiverRequest;
use App\Models\Request as UserRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;
use App\NotificationType;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Laravel\Firebase\Facades\Firebase;
use App\Models\Notification;
use App\Models\User;
use App\Models\ValetTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FeeWaiverService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    use CheckRequestStatus;

    public function requestFeeWaiver(int $requestId)
    {
        $user = Auth::user();

        DB::beginTransaction();

        try {
            $this->checkRequestStatusInPreparing($requestId);
            $request = UserRequest::find($requestId);

            if (!$request) {
                throw new \Exception(trans("Request not found."));
            }

            if ($request->user_id !== $user->id) {
                throw new \Exception(trans("You are not authorized to request a fee waiver for this request."));
            }

            $existingRequest = FeeWaiverRequest::where('user_id', $user->id)
                ->where('request_id', $requestId)
                ->first();

            if ($existingRequest) {
                throw new \Exception(trans("You already sent a request."));
            }

            $feeWaiver = FeeWaiverRequest::create([
                'user_id' => $user->id,
                'request_id' => $requestId,
                'status' => 'pending',
            ]);

            Log::info("User {$user->id} requested fee waiver for request {$requestId}.");

            $valetRequest = UserRequest::with('location')->find($requestId);
            if (!$valetRequest || !$valetRequest->location) {
                throw new \Exception('Request or location not found.');
            }

            $hostId = $valetRequest->location->host_id;
            $valetHost = User::with('fcmTokens')->find($hostId);

            if (!$valetHost || !$valetHost->hasRole('valet_host')) {
                throw new \Exception('Valet host not found or does not have the valet_host role.');
            }

            $tokens = $valetHost->fcmTokens->pluck('token')->toArray();

            DB::commit();

            event(new FeeWaiverRequested($feeWaiver, $tokens));

            return $feeWaiver;

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }



    public function handleFeeWaiver(FeeWaiverRequest $feeWaiver, string $action): FeeWaiverRequest
{
    DB::beginTransaction();

    try {
        if ($feeWaiver->status !== 'pending') {
            throw new \Exception('This request has already been processed.');
        }

        $user = Auth::user();

        $userRequest = UserRequest::with(['payment', 'location'])->findOrFail($feeWaiver->request_id);
        $location = $userRequest->location;

        $isPlatformManager = $user->hasRole('platform_manager');
        $isAssignedHost = $user->hasRole('valet_host') && $location && $location->host_id == $user->id;

        if (!($isPlatformManager || $isAssignedHost)) {
            throw new \Exception('You are not authorized to handle this fee waiver.');
        }

        $status = $action === 'approve' ? 'approved' : 'rejected';

        $feeWaiver->update([
            'status' => $status,
            'approved_by' => $user->id,
        ]);

        if ($status === 'approved') {
            $userRequest->update(['fee_waived' => true]);

            $valetTime = ValetTime::where('user_id', $userRequest->user_id)->first();
            $valetTime?->update(['price' => 0]);

            $userRequest->payment()->create([
                'amount' => 0,
                'payment_method' => 'FREE',
                'status' => 'completed'
            ]);
        }

        Log::info("Fee waiver {$feeWaiver->id} {$status} by user {$user->id}");

        $customer = $userRequest->user;
        $tokens = $customer->fcmTokens()->pluck('token')->toArray();

        DB::commit();

        event(new FeeWaiverHandled($feeWaiver, $status, $tokens));

        return $feeWaiver;

    } catch (\Throwable $e) {
        DB::rollBack();
        throw $e;
    }
}
}
