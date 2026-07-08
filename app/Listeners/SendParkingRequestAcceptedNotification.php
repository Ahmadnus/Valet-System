<?php

namespace App\Listeners;

use App\Events\ParkingRequestAccepted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\NotificationType;
use Illuminate\Support\Facades\Log;

class SendParkingRequestAcceptedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ParkingRequestAccepted $event): void
{
    $request = $event->request;
    $customer = $request->user;

    $tokens = $customer->fcmTokens->pluck('token')->toArray();

    if (!empty($tokens)) {
        app(\App\Services\NotificationService::class)->sendNotificationToTokens(
            title: trans('Request Accepted'),
            body: trans('Your parking request has been accepted by a valet agent.'),
            tokens: $tokens,
            data: [
                'request_id' => $request->id,
                'status' => $request->status,
            ],
            type: NotificationType::ACCEPT_REQUESTED
        );

        Log::info("📬 Sent parking acceptance notification for request ID {$request->id} to user ID {$customer->id}.", [
            'tokens' => implode(',', $tokens),
        ]);
    } else {
        Log::warning("⚠️ No FCM tokens found for user ID {$customer->id} to send parking acceptance notification.");
    }
}
}