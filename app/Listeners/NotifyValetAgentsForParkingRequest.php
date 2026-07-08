<?php

namespace App\Listeners;

use App\Events\ParkingRequestCreated;
use App\Models\Location;
use App\Services\NotificationService;
use App\NotificationType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyValetAgentsForParkingRequest implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(protected NotificationService $notificationService) {}

    public function handle(ParkingRequestCreated $event): void
    {
        $request = $event->request;
        $location = Location::find($request->location_id);

        if (!$location) return;

        $agents = $location->agents()
            ->where('status', 'available')
            ->whereHas('fcmTokens')
            ->get();

        $notifiedAgents = $agents->filter(fn($agent) => $agent->getRoleNames()->contains('valet_agent'));

        $tokens = $notifiedAgents->flatMap(fn($agent) => $agent->fcmTokens->pluck('token'))->toArray();
        $tokensString = implode(',', $tokens);

        if (!empty($tokens)) {
            Log::info("📣 Notifying valet agents for request {$request->id}", [
                'agents' => $notifiedAgents->pluck('id', 'name')->toArray(),
                'tokens_string' => $tokensString,
            ]);

            $this->notificationService->sendNotificationToTokens(
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
