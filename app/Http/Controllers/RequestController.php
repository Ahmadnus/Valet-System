<?php

namespace App\Http\Controllers;

use App\app\Traits\ApiResponse;
use App\Http\Requests\CompleteValetRequest as RequestsCompleteValetRequest;
use App\Http\Requests\PreparingCarRequests;
use App\Http\Requests\StoreRequestRequest;
use App\Http\Resources\Request\RequestResource;
use App\Services\Requests\ParkingRequestService;
use App\Services\Requests\CompleteValetRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Exception;

class RequestController extends Controller
{
    use ApiResponse;
    use AuthorizesRequests;

    protected $parkingRequestService;

    public function __construct(ParkingRequestService $parkingRequestService)
    {
        $this->parkingRequestService = $parkingRequestService;
    }

    public function createParkingRequest(StoreRequestRequest $request)
{
     // Get current language
     $lang = app()->getLocale();
    try {

        $parkingRequest = $this->parkingRequestService->createParkingRequest($request->all());
        return $this->successResponse(trans('Parking request created successfully'), new RequestResource($parkingRequest), 201);
    } catch (Exception $e) {
        return $this->errorResponse($e->getMessage());
    }
}

public function acceptParkingRequest(int $requestId)
{
    $lang = app()->getLocale(); // Get current language
    try {
        $parkingRequest = $this->parkingRequestService->acceptParkingRequest($requestId);
        return $this->successResponse(trans('Parking request accepted successfully'), new RequestResource($parkingRequest));
    } catch (Exception $e) {
        return $this->errorResponse($e->getMessage());
    }
}

public function startParkingRequest(int $requestId)
{
    $lang = app()->getLocale(); // Get current language
    try {
        $request = $this->parkingRequestService->startParkingRequest($requestId);
        return $this->successResponse(trans('Parking request started successfully'), new RequestResource($request));
    } catch (Exception $e) {
        return $this->errorResponse($e->getMessage());
    }
}

public function requestCarPreparation(PreparingCarRequests $request, int $requestId)
{
    try {
        $parkingRequest = $this->parkingRequestService->requestCarPreparation(
            $requestId,
            $request->latitude,
            $request->longitude
        );

        return $this->successResponse(
            trans('Car preparation requested successfully'),
            new RequestResource($parkingRequest)
        );
    } catch (Exception $e) {
        return $this->errorResponse($e->getMessage());
    }
}
public function acceptDeliveryRequest(int $requestId)
{
    $lang = app()->getLocale(); // Get current language
    try {
        $parkingRequest = $this->parkingRequestService->acceptDeliveryRequest($requestId);
        return $this->successResponse(trans('Car delivery accepted successfully'), new RequestResource($parkingRequest));
    } catch (Exception $e) {
        return $this->errorResponse($e->getMessage());
    }
}

public function completeParkingRequest(int $id, RequestsCompleteValetRequest $request)
{
    $lang = app()->getLocale(); // Get current language
    try {
        $validated = $request->validated();

        $parkingRequest = $this->parkingRequestService->completeValet($id, $request->validated());

        return $this->successResponse(trans('Valet completed successfully'), new RequestResource($parkingRequest));
    } catch (Exception $e) {
        return $this->errorResponse($e->getMessage());
    }
}

public function cancelParkingRequest(Request $request, int $requestId)
{
    $lang = app()->getLocale(); // Get current language
    $request->validate([
        'reason' => 'required|string|max:255',
    ]);

    $reason = $request->input('reason');
    try {
        $parkingRequest = app(ParkingRequestService::class)->cancelParkingRequest($requestId, $reason);
        return response()->json(['message' => trans('Parking request cancelled successfully')], 200);
    } catch (\Exception $e) {

        return response()->json(['message' => $e->getMessage()], 400);
    }}



}
