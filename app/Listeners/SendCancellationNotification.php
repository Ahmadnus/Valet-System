<?php

namespace App\Listeners;

use App\Events\ParkingRequestCanceled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\NotificationType;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
class SendCancellationNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ParkingRequestCanceled $event): void
{
    $request = $event->request;
    $reason = $event->reason;
    $customer = $request->user;

    $tokens = $customer->fcmTokens->pluck('token')->toArray();

    if (!empty($tokens)) {
        app('App\Services\NotificationService')->sendNotificationToTokens(
            title: trans('Car Preparation Requested'),
            body: trans('A customer has requested their car to be prepared.'),
            tokens: $tokens,
            data: [
                'request_id' => $request->id,
                'status' => $request->status,

            ],
            type: NotificationType::CANCEL_REQUEST
        );

        Log::info("📣 Sent cancellation notification for request ID {$request->id} to user ID {$customer->id}.", [
            'tokens' => implode(',', $tokens),
            'reason' => $reason,
        ]);
    } else {
        Log::warning("⚠️ No tokens found for user ID {$customer->id} when trying to send cancellation notification.");
    }
}

}