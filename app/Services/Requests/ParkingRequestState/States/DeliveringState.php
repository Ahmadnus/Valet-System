<?php

namespace App\Services\Requests\ParkingRequestState\States;


use App\Events\DeliveryRequestAccepted;
use App\Events\TruckingCar;
use App\Models\LocationAgent;
use App\Models\Request;
use App\Services\Requests\ParkingRequestState\ParkingRequestStateInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DeliveringState implements ParkingRequestStateInterface
{

    public function handle(Request $request, array $data = [])
    {
        $user = Auth::user();

        if (!$user->hasRole('valet_agent')) {
            Log::warning("User {$user->id} attempted to deliver request {$request->id} without valet_agent role.");
            throw new \Exception(trans('Unauthorized: You must be a valet agent to deliver this request.'));
        }

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
        event(new TruckingCar($agentId, $latitude, $longitude)); // 🔥 real location here

        return $request;
    }}