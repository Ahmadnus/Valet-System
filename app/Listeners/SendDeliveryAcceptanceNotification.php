<?php

namespace App\Listeners;

use App\Events\DeliveryRequestAccepted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;


use App\NotificationType;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
class SendDeliveryAcceptanceNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(protected NotificationService $notificationService) {}

    public function handle(DeliveryRequestAccepted $event): void
{
    $request = $event->request;
    $customer = $request->user;

    $tokens = $customer->fcmTokens->pluck('token')->toArray();

    Log::debug("📡 Tokens found:", $tokens);

    if (!empty($tokens)) {
        app(\App\Services\NotificationService::class)->sendNotificationToTokens(
            title: trans('Request Accepted'),
            body: trans('Your delivery request has been accepted by a valet agent.'),
            tokens: $tokens,
            data: [
                'request_id' => $request->id,
                'status' => \App\Models\Request::STATUS_ACCEPTED,
            ],
            type: NotificationType::ACCEPT_PREPARING
        );
    }
}

}