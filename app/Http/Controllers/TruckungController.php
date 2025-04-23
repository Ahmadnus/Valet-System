<?php

namespace App\Http\Controllers;


use App\Events\TruckingCar;
use App\Models\AgentLocation;
use App\Models\Car;
use App\Models\Request as ModelRequest;
use Illuminate\Http\Request ;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\location\TruckingRequest;
use App\Http\Resources\AgentLocationResource;
use App\Services\TruckingService;
use Exception;
use App\app\Traits\ApiResponse;



class TruckungController extends Controller
{
    use ApiResponse;

    protected $trackingService;

    public function __construct(TruckingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    public function updateLocation(TruckingRequest $request)
    {
        try {
            $lang = app()->getLocale();
            $this->trackingService->updateLocation($request->validated());
            return $this->successResponse('Location updated');
        }




        catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function getLocation($agentId)
    {
        $lang = app()->getLocale();
        try {
            $location = $this->trackingService->getLocation($agentId);
            return $this->successResponse(trans('Location found'), new AgentLocationResource($location));
        } catch (Exception $e) {
            return $this->errorResponse(trans('Location not found'), 404);
        }
    }


}