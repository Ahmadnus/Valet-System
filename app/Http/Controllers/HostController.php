<?php

namespace App\Http\Controllers;

use App\Models\Host;
use App\Http\Requests\StoreHostRequest;
use App\Http\Requests\UpdateHostRequest;
use App\Http\Resources\CustomerResource;
use App\Services\hostsServices;
use App\app\Traits\ApiResponse;

class HostController extends Controller
{ use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    protected $valetHostService;

    public function __construct(hostsServices $valetHostService)
    {
        $this->valetHostService = $valetHostService;
    }
    public function indexAgents()
    {
        try {
            $locations = $this->valetHostService->getAgentsForHost();
            return $this->successResponse('Agents fetched successfully.', $locations);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
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
    public function store(StoreHostRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Host $host)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Host $host)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHostRequest $request, Host $host)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Host $host)
    {
        //
    }
}
