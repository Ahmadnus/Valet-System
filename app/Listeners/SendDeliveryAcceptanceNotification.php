<?php

namespace App\Listeners;

use App\Events\DeliveryRequestAccepted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;


use App\NotificationType;
use Illuminate\Support\Facades\Log;
class SendDeliveryAcceptanceNotification
{
    public function handle(DeliveryRequestAccepted $event): void
{
    $request = $event->request;
    $customer = $request->user;

    $tokens = $customer->fcmTokens->pluck('token')->toArray();

    if (!empty($tokens)) {
        app('App\Services\NotificationService')->sendMulticastNotification(
            title: trans('Request Accepted'),
            body: trans('Your delivery request has been accepted by a valet agent.'),
            tokens: $tokens,
            data: [
                'request_id' => $request->id,
                'status' => \App\Models\Request::STATUS_ACCEPTED,
            ],
            type: NotificationType::ACCEPT_PREPARING
        );

        Log::info("Sent acceptance notification for request ID {$request->id} to user ID {$customer->id}.", [
            'tokens' => $tokens,
        ]);
    }
}
}