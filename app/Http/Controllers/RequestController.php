<?php

namespace App\Http\Controllers;

use App\app\Traits\ApiResponse;
use App\Http\Requests\CompleteValetRequest as RequestsCompleteValetRequest;
use App\Http\Requests\PreparingCarRequests;
use App\Http\Requests\StoreRequestRequest;
use App\Http\Requests\UpdateParkingRequestStatusRequest;
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
        $lang = app()->getLocale();

        try {
            $parkingRequest = $this->parkingRequestService->createParkingRequest($request->validated());

            return $this->successResponse(
                trans('Parking request created successfully'),
                new RequestResource($parkingRequest),
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function updateStatus(UpdateParkingRequestStatusRequest $request, int $userRequest)
    {
        try {
            $validated = $request->validated(); // استخراج البيانات المتحققة فقط

            $parkingRequest = $this->parkingRequestService->changeParkingRequestStatus(
                $userRequest,
                $validated['status'] ?? null,
                $validated
            );

            return $this->successResponse(
                trans("Request updated to status {$validated['status']} successfully"),
                new RequestResource($parkingRequest)
            );

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }


}
