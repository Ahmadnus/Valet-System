<?php

namespace App\Http\Controllers;

use App\app\Traits\ApiResponse;
use App\Http\Requests\FindNearestLocationRequest;
use App\Http\Requests\Location\LocationRequest;
use App\Http\Resources\Location\LocationResource;
use App\Models\Location;
use App\Models\ParkingSlot;
use App\Services\LocationService;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateLocationRequest;
class LocationController extends Controller
{  use ApiResponse;
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function findNearest(FindNearestLocationRequest $request, LocationService $locationService)
    {

        try {
            $lang = app()->getLocale();
            $locations = $locationService->findNearestLocation($request);
            return $this->successResponse(trans('Nearest locations retrieved successfully'),$locations);
        } catch (\DomainException | \InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function addLocation(LocationRequest $request, LocationService $locationService)
    {
        try {
            $lang = app()->getLocale();
            $location = $locationService->addLocation($request);
            return $this->successResponse(trans('Location created successfully'), $location, 201);
        } catch (\DomainException | \InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function update(UpdateLocationRequest $request, $id)
    {
        try {
            $lang = app()->getLocale();
            $location = $this->locationService->updateLocation($request, $id);
            return $this->successResponse(trans('Location updated successfully'), $location);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function destroy($id)
    {
        try {
            $lang = app()->getLocale();
            $this->locationService->deleteLocation($id);
            return $this->successResponse(trans('Location deleted successfully'), null);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function show($id)
    {
        try {
            $lang = app()->getLocale();
            $location = $this->locationService->getLocation($id);
            return $this->successResponse(trans('Location retrieved successfully'), $location);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function index()
    {
        try {
            $lang = app()->getLocale();
            $locations = $this->locationService->getAllLocations();
            return $this->successResponse(trans('All locations retrieved successfully'), $locations);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

}
