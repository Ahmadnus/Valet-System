<?php

namespace App\Listeners;


use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\ParkingRequestCreated;
use App\Models\Location;
use App\NotificationType;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
class NotifyValetAgentsForParkingRequest
{
    public function __construct(protected NotificationService $notificationService) {}

    public function handle(ParkingRequestCreated $event)
    {
        $request = $event->request;
        $location = Location::find($request->location_id);

        if (!$location) return;

        $agents = $location->agents()
            ->where('status', 'available')
            ->whereHas('fcmTokens')
            ->get();

        $notifiedAgents = $agents->filter(function ($agent) {
            return $agent->getRoleNames()->contains('valet_agent');
        });

        $tokens = $notifiedAgents->flatMap(function ($agent) {
            return $agent->fcmTokens->pluck('token');
        })->toArray();

        if ($notifiedAgents->isNotEmpty()) {
            Log::info('Valet agents notified for request ID ' . $request->id, [
                'agents' => $notifiedAgents->pluck('id', 'name')->toArray(),
                'tokens' => $tokens,
            ]);
        }

        if (!empty($tokens)) {
            $this->notificationService->sendMulticastNotification(
                title: trans('New Parking Request'),
                body: trans('A new parking request has been created by a customer.'),
                tokens: $tokens,
                data: [
                    'request_id' => $request->id,
                    'status' => 'pending',
                ],
                type: NotificationType::REQUEST_PARKING_SERVICE
            );
        }
    }
}
