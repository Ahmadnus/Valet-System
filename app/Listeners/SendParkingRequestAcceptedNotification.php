<?php

namespace App\Listeners;

use App\Events\ParkingRequestAccepted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\NotificationType;
use Illuminate\Support\Facades\Log;

class SendParkingRequestAcceptedNotification
{
    public function handle(ParkingRequestAccepted $event): void
{
    $request = $event->request;
    $customer = $request->user;

    $tokens = $customer->fcmTokens->pluck('token')->toArray();

    if (!empty($tokens)) {
        app('App\Services\NotificationService')->sendMulticastNotification(
            title: trans('Request Accepted'),
            body: trans('Your parking request has been accepted by a valet agent.'),
            tokens: $tokens,
            data: [
                'request_id' => $request->id,
                'status' => $request->status,
            ],
            type: NotificationType::ACCEPT_REQUESTED
        );

        Log::info("Sent acceptance notification for request ID {$request->id} to user ID {$customer->id}.", [
            'tokens' => $tokens,
        ]);
    }
}
}