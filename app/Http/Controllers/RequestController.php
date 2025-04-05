<?php

namespace App\Http\Controllers;

use App\app\Traits\ApiResponse;
use App\Http\Requests\StoreRequestRequest;
use App\Http\Resources\Request\RequestResource;
use App\Services\Requests\ParkingRequestService;
use Illuminate\Http\Request;
use Exception;

class RequestController extends Controller
{
    use ApiResponse;

    protected $parkingRequestService;

    public function __construct(ParkingRequestService $parkingRequestService)
    {
        $this->parkingRequestService = $parkingRequestService;
    }

    public function createParkingRequest(StoreRequestRequest $request)
    {
        try {
            $parkingRequest = $this->parkingRequestService->createParkingRequest($request->all());
            return $this->successResponse('Parking request created successfully', new RequestResource($parkingRequest), 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function acceptParkingRequest(int $requestId)
    {
        try {
            $parkingRequest = $this->parkingRequestService->acceptParkingRequest($requestId);
            return $this->successResponse('Parking request accepted successfully', new RequestResource($parkingRequest));
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function requestCarPreparation(int $requestId)
    {
        try {
            $parkingRequest = $this->parkingRequestService->requestCarPreparation($requestId);
            return $this->successResponse('Car preparation requested successfully', new RequestResource($parkingRequest));
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function completeParkingRequest(int $requestId, Request $request)
    {
        try {
            $parkingRequest = $this->parkingRequestService->completeParkingRequest($requestId, $request->plate_number);
            return $this->successResponse('Parking request completed successfully', new RequestResource($parkingRequest));
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}