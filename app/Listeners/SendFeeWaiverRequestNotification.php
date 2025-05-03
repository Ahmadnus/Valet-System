<?php

namespace App\Listeners;

use App\Events\FeeWaiverRequested;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\NotificationType;
use Illuminate\Support\Facades\Log;
class SendFeeWaiverRequestNotification
{
    public function handle(FeeWaiverRequested $event)
{
    app('App\Services\NotificationService')->sendMulticastNotification(
        title: trans('Fee Waiver Request'),
        body: trans('A customer has requested a fee waiver for a parking service.'),
        tokens: $event->tokens,
        data: [
            'request_id' => $event->feeWaiver->request_id,
            'status' => 'pending',
        ],
        type: NotificationType::ASK_FOR_WAIVE
    );

    Log::info("Sent fee waiver request notification for request ID {$event->feeWaiver->request_id}", [
        'tokens' => $event->tokens,
    ]);
}
}