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
use App\Models\ValetTime;

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

        if ($existingRequest) {
            throw new \Exception(trans("You have already requested a fee waiver for this request."));
        }


        $feeWaiver = FeeWaiverRequest::create([
            'user_id' => $user->id,
            'request_id' => $requestId,
            'status' => 'pending',
        ]);


        Log::info("User {$user->id} requested fee waiver for request {$requestId}.");


        $this->notificationService->sendNotification(
            title: 'Fee Waiver Request',
            body: 'A customer has requested a fee waiver for a parking service.',
            data: [
                'request_id' => $requestId,
                'status' => 'pending',
            ],
            user: null,
            topic: \App\TopicType::VALET_HOST,
            type: NotificationType::ASK_FOR_WAIVE
        );

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

    // Notify the customer
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
