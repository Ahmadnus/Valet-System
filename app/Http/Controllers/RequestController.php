<?php

namespace App\Http\Controllers;

use App\Http\Resources\Request\RequestResource;
use App\Services\Requests\ParkingRequestService;
use Illuminate\Http\Request;
use Exception;

class RequestController extends Controller
{
    protected $parkingRequestService;

    public function __construct(ParkingRequestService $parkingRequestService)
    {
        $this->parkingRequestService = $parkingRequestService;
    }

    public function createParkingRequest(Request $request)
    {
        try {
            $parkingRequest = $this->parkingRequestService->createParkingRequest($request->all());
            return response()->json(new RequestResource($parkingRequest), 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function acceptParkingRequest(int $requestId)
    {
        try {
            $parkingRequest = $this->parkingRequestService->acceptParkingRequest($requestId);
            return response()->json(new RequestResource($parkingRequest), 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function requestCarPreparation(int $requestId)
    {
        try {
            $parkingRequest = $this->parkingRequestService->requestCarPreparation($requestId);
            return response()->json(new RequestResource($parkingRequest), 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function completeParkingRequest(int $requestId, Request $request)
    {
        try {
            $parkingRequest = $this->parkingRequestService->completeParkingRequest($requestId, $request->plate_number);
            return response()->json(new RequestResource($parkingRequest), 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
