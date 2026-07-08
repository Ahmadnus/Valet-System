<?php

namespace App\Listeners;

use App\Events\FeeWaiverHandled;
use App\NotificationType;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendFeeWaiverHandledNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(FeeWaiverHandled $event)
{
    $statusTranslation = trans($event->status === 'approved' ? 'approved' : 'rejected');

    $body = trans('Your fee waiver request has been :status.', [
        'status' => $statusTranslation
    ]);

    app(\App\Services\NotificationService::class)->sendNotificationToTokens(
        title: trans('Fee Waiver'),
        body: $body,
        tokens: $event->tokens,
        data: [
            'request_id' => $event->feeWaiver->request_id,
            'status' => $event->status,
        ],
        type: NotificationType::ANSWER_WAIVE
    );

    Log::info("📩 Sent fee waiver {$event->status} notification for request ID {$event->feeWaiver->request_id}", [
        'tokens' => implode(',', $event->tokens),
    ]);
}
}