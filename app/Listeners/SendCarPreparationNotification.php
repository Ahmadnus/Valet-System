<?php

namespace App\Listeners;

use App\Events\CarPreparationRequested;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\NotificationType;
use App\Models\Location;
use Illuminate\Support\Facades\Log;

class SendCarPreparationNotification
{
    public function handle(CarPreparationRequested $event): void
    {
        $request = $event->request;
        $latitude = $event->latitude;
        $longitude = $event->longitude;

        $location = Location::find($request->location_id);
        $agents = $location->agents()
            ->where('status', 'available')
            ->whereHas('fcmTokens')
            ->get();

        $tokens = $agents->filter(fn($agent) => $agent->getRoleNames()->contains('valet_agent'))
            ->flatMap(fn($agent) => $agent->fcmTokens->pluck('token'))
            ->toArray();

        if (!empty($tokens)) {
            app('App\Services\NotificationService')->sendNotificationToTokens(
                title: trans('Car Preparation Requested'),
                body: trans('A customer has requested their car to be prepared.'),
                tokens: $tokens,
                data: [
                    'request_id' => $request->id,
                    'status' => $request->status,
                    'customer_latitude' => $latitude,
                    'customer_longitude' => $longitude,
                ],
                type: NotificationType::CAR_PREPARATION_REQUESTED
            );

            Log::info("Car preparation notification sent to available agents in location ID {$location->id}.", [
                'tokens' => $tokens,
            ]);
        }
    }
}
