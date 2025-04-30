<?php

namespace App\Services;

use App\app\traits\CheckRequestStatus;
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

        // 1. Ensure the request is still in 'preparing' state
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

if($existingRequest ){
    throw new \Exception(trans("You are already send."));
}



        $feeWaiver = FeeWaiverRequest::create([
            'user_id' => $user->id,
            'request_id' => $requestId,
            'status' => 'pending',
        ]);


        Log::info("User {$user->id} requested fee waiver for request {$requestId}.");
        $valetHosts = UserRequest::where('id', $requestId)
        ->with('location')
        ->first();
        $host=$valetHosts->location->host_id;


        $valetHostss = User::role('valet_host')->where('id',$host)->with('fcmTokens')
        ->first();

$tokens[]=$valetHostss->fcmTokens->pluck('token');
    if (!empty($tokens)) {
        $this->notificationService->sendMulticastNotification(
            title: 'Fee Waiver Request',
            body: 'A customer has requested a fee waiver for a parking service.',
            tokens: $tokens,
            data: [
                'request_id' => $requestId,
                'status' => 'pending',
            ],
            type: NotificationType::ASK_FOR_WAIVE
        );

        Log::info("Sent fee waiver request notification for request ID {$requestId} to valet hosts.", [
            'tokens' => $tokens,
        ]);
    }
        return $feeWaiver;
    }




public function handleFeeWaiver(FeeWaiverRequest $feeWaiver, string $action): FeeWaiverRequest
{
    if ($feeWaiver->status !== 'pending') {
        throw new \Exception('This request has already been processed.');
    }

    $status = $action === 'approve' ? 'approved' : 'rejected';

    $feeWaiver->update([
        'status' => $status,
        'approved_by' => Auth::id(),
    ]);

    if ($status === 'approved') {

        $userRequest = UserRequest::with('payment')->findOrFail($feeWaiver->request_id);
        $userRequest->update(['fee_waived' => true]);

        $valetTime = ValetTime::where('user_id',$userRequest ->user_id )->first();
        $valetTime->update(['price' => 0]);

            $userRequest->payment()->create([

                'amount' =>     0,
                'payment_method' => 'FREE',
                'status'=>'completed'

            ]);


    }

    Log::info("Fee waiver {$feeWaiver->id} {$status} by user " . Auth::id());


    $this->notificationService->sendNotification(
        title: 'Fee Waiver ' ,
        body: "Your fee waiver request has been {$status}.",
        data: [
            'request_id' => $feeWaiver->request_id,
            'status' => $status,
        ],
        user: $feeWaiver->user,
        topic:  \App\TopicType::VALET_HOST,
        type: NotificationType::ANSWER_WAIVE
    );

    return $feeWaiver;
}
}
