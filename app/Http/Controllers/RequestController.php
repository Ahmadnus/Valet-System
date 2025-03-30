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
//this response for delete
    private function successResponse($data, $message, $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    private function errorResponse($message, $status = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null
        ], $status);
    }

    public function createParkingRequest(StoreRequestRequest $request)
    {
        try {
            $parkingRequest = $this->parkingRequestService->createParkingRequest($request->all());
            return $this->successResponse(new RequestResource($parkingRequest), 'Parking request created successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function acceptParkingRequest(int $requestId)
    {
        try {
            $parkingRequest = $this->parkingRequestService->acceptParkingRequest($requestId);
            return $this->successResponse(new RequestResource($parkingRequest), 'Parking request accepted successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function requestCarPreparation(int $requestId)
    {
        try {
            $parkingRequest = $this->parkingRequestService->requestCarPreparation($requestId);
            return $this->successResponse(new RequestResource($parkingRequest), 'Car preparation requested successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function completeParkingRequest(int $requestId, Request $request)
    {
        try {
            $parkingRequest = $this->parkingRequestService->completeParkingRequest($requestId, $request->plate_number);
            return $this->successResponse(new RequestResource($parkingRequest), 'Parking request completed successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

}
