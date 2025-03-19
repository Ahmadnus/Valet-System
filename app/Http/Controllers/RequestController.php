<?php

namespace App\Http\Controllers;

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
            return response()->json(['success' => true, 'message' => 'Parking request sent successfully!', 'data' => $parkingRequest], 201);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function acceptParkingRequest(int $requestId)
    {
        try {
            $parkingRequest = $this->parkingRequestService->acceptParkingRequest($requestId);
            return response()->json(['success' => true, 'message' => 'Parking request accepted successfully!', 'data' => $parkingRequest], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function requestCarPreparation(int $requestId)
    {
        try {
            $parkingRequest = $this->parkingRequestService->requestCarPreparation($requestId);
            return response()->json(['success' => true, 'message' => 'Car preparation requested successfully!', 'data' => $parkingRequest], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function completeParkingRequest(int $requestId, Request $request)
    {
        try {
            $plateNumber = $request->input('plate_number'); // Get plate number from request
            $parkingRequest = $this->parkingRequestService->completeParkingRequest($requestId, $plateNumber);
            return response()->json(['success' => true, 'message' => 'Parking request completed successfully!', 'data' => $parkingRequest], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
