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
class FeeWaiverService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    use CheckRequestStatus;

    public function requestFeeWaiver(int $requestId): FeeWaiverRequest
    {
        $user = Auth::user();

        // Check the request status using the trait method
        $this->checkRequestStatusInPreparing($requestId);

        $feeWaiver = FeeWaiverRequest::create([
            'user_id' => $user->id,
            'request_id' => $requestId,
            'status' => 'pending',
        ]);

        Log::info("User {$user->id} requested fee waiver for request {$requestId}.");

        // Send notification to valet host
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
        $userRequest = UserRequest::findOrFail($feeWaiver->request_id);
        $userRequest->update(['fee_waived' => true]);
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
