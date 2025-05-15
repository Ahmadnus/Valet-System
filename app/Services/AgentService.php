<?php

namespace App\Services;
use App\Models\LocationAgent;
use App\Models\ParkingSlot;
use App\Models\Request;
use Illuminate\Support\Facades\Auth;
class AgentService
{
    /**
     * Create a new class instance.
     */
    public function getAssignedParkingSlots()
    {
        $agent = Auth::user();

        $locationAgent = LocationAgent::where('agent_id', $agent->id)->first();

        if (!$locationAgent) {
            throw new \Exception(trans('No active location assigned to this agent.'));
        }

        return ParkingSlot::where('location_id', $locationAgent->location_id)->get();
    }

    public function getAgentRequests()
    {
        $agent = Auth::user();

        return Request::where('valet_agent_id', $agent->id)
            ->where('status', '!=', Request::STATUS_PENDING)
            ->with(['user', 'car']) // if you want to include related models
            ->latest()
            ->get();
    }
}
