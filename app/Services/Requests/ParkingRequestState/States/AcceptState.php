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
class AcceptState implements ParkingRequestStateInterface
{
    $user = Auth::user();

    if (!$user->hasRole('valet_agent')) {
        Log::warning("User {$user->id} attempted to accept request {$request->id} without valet_agent role.");
        throw new \Exception(trans('Unauthorized: You must be a valet agent to accept this request.'));
    }

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

    return $request;

}