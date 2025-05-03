<?php

namespace App\Listeners;

use App\Events\ParkingRequestCanceled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\NotificationType;
use Illuminate\Support\Facades\Log;
class SendCancellationNotification
{
    public function handle(ParkingRequestCanceled $event): void
    {
        $request = $event->request;
        $reason = $event->reason;
        $customer = $request->user;

        $tokens = $customer->fcmTokens->pluck('token')->toArray();

        if (!empty($tokens)) {
            app('App\Services\NotificationService')->sendMulticastNotification(
                title: trans('Request Canceled'),
                body: trans('Your parking request has been canceled by the valet agent.'),
                tokens: $tokens,
                data: [
                    'request_id' => $request->id,
                    'status' => \App\Models\Request::STATUS_CANCELED,
                    'reason' => $reason,
                ],
                type: NotificationType::CANCEL_REQUEST
            );

            Log::info("Sent cancellation notification for request ID {$request->id} to user ID {$customer->id}.", [
                'tokens' => $tokens,
                'reason' => $reason,
            ]);
        }
    }
}