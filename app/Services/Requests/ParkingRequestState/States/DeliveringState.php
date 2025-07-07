<?php

namespace App\Services\Requests\ParkingRequestState\States;


use App\Events\DeliveryRequestAccepted;
use App\Events\TruckingCar;
use App\Models\AgentLocation;
use App\Models\LocationAgent;
use App\Models\Request;
use App\Services\Requests\ParkingRequestState\ParkingRequestStateInterface;
use App\Traits\app\Traits\ChecksValetAgentRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DeliveringState implements ParkingRequestStateInterface
{

    use ChecksValetAgentRole;

    public function handle(Request $request, array $data = [])
    {
        $user = $this->ensureValetAgent('Deliver Request');
        $agentId = $user->id;

        if ($request->status !== Request::STATUS_PREPARING) {
            Log::warning("Agent $agentId tried to deliver request {$request->id} but it is not preparing.");
            throw new \Exception(trans("You can only accept delivery when request is preparing."));
        }

        $latitude = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;

        if (is_null($latitude) || is_null($longitude)) {
            throw new \Exception(trans('Location data is required.'));
        }

        $request->update([
            'status' => Request::STATUS_DELIVERING,
        ]);

        LocationAgent::where('agent_id', $agentId)
            ->where('location_id', $request->location_id)
            ->update(['status' => 'in_way']);

        Log::info("Agent $agentId accepted delivery for request {$request->id}.");

        event(new DeliveryRequestAccepted($request));

        AgentLocation::create([
            'agent_id' => $agentId,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);

        event(new TruckingCar($agentId, $latitude, $longitude));

        return $request;
    }
}