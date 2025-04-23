<?php

namespace App\Http\Controllers;

use App\app\Traits\ApiResponse;
use App\Models\Agent;
use App\Http\Requests\StoreAgentRequest;
use App\Http\Requests\UpdateAgentRequest;
use App\Http\Resources\ParkingSlotResource;
use App\Http\Resources\Request\RequestResource;
use App\Models\LocationAgent;
use App\Models\ParkingSlot;
use App\Models\Request;
use App\Services\AgentService;
use Illuminate\Support\Facades\Auth;

class AgentController extends Controller
{
    /**
     * Display a listing of the resource.
     */




     use ApiResponse;

     public function __construct(protected AgentService $agentDashboardService) {}

     public function dashboard()
     {
         try {
             $slots = $this->agentDashboardService->getAssignedParkingSlots();
             $requests = $this->agentDashboardService->getAgentRequests();

             return $this->successResponse(
                 'Agent dashboard data retrieved.',
                 [
                     'parking_slots' => ParkingSlotResource::collection($slots),
                     'requests' => RequestResource::collection($requests),
                 ]
             );

         } catch (\Exception $e) {
             return $this->errorResponse($e->getMessage(), 404);
         }
     }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAgentRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Agent $agent)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Agent $agent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAgentRequest $request, Agent $agent)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Agent $agent)
    {
        //
    }
}
