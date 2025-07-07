<?php

namespace App\Services\Requests\ParkingRequestState\States;

use App\Models\LocationAgent;
use App\Models\Request;
use App\Services\Requests\ParkingRequestState\ParkingRequestStateInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Events\DeliveryRequestAccepted;
use App\Events\ParkingRequestAccepted;
use App\Events\TruckingCar;
use App\Models\AgentLocation;
use App\Traits\app\Traits\ChecksValetAgentRole;

class AcceptState implements ParkingRequestStateInterface
{
    use ChecksValetAgentRole;

    public function handle(Request $request, array $data = [])
    {
        $user = $this->ensureValetAgent('Accept Request');
        $agentId = $user->id;

        $agentStatus = LocationAgent::where('agent_id', $agentId)
            ->where('location_id', $request->location_id)
            ->value('status');

        if ($agentStatus !== 'available') {
            Log::warning("Agent $agentId is not available to accept request {$request->id}.");
            throw new \Exception(trans('You cannot accept the request because you are not available at the moment.'));
        }

        $request->update([
            'status' => Request::STATUS_ACCEPTED,
            'valet_agent_id' => $agentId,
            'in_progress_time' => now(),
        ]);

        LocationAgent::where('agent_id', $agentId)
            ->where('location_id', $request->location_id)
            ->update(['status' => 'occupied']);

        Log::info("Request {$request->id} accepted by valet agent $agentId.");
        event(new ParkingRequestAccepted($request));

        $latitude = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;

        if (is_null($latitude) || is_null($longitude)) {
            throw new \Exception(trans('Location data is required.'));
        }

        AgentLocation::create([
            'agent_id' => $agentId,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);

        event(new TruckingCar($agentId, $latitude, $longitude));

        return $request;
    }
}