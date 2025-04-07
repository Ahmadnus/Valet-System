<?php

namespace App\Http\Controllers;

use App\app\Traits\ApiResponse;
use App\Models\FeeWaiverRequest;
use App\Models\Request as UserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RequestFeeWaiverRequest;
use App\Http\Requests\HandleFeeWaiverRequest;

use App\Services\FeeWaiverService;

class FeeWaiverController extends Controller
{
    use ApiResponse;

    protected FeeWaiverService $feeWaiverService;

    public function __construct(FeeWaiverService $feeWaiverService)
    {
        $this->feeWaiverService = $feeWaiverService;
    }

    public function requestFeeWaiver(RequestFeeWaiverRequest $request)
    {
        try {
            $feeWaiver = $this->feeWaiverService->requestFeeWaiver($request->validated()['request_id']);

            return $this->successResponse('Fee waiver request sent.', $feeWaiver);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function handleFeeWaiver(HandleFeeWaiverRequest $request, $id)
    {
        try {
            $feeWaiver = FeeWaiverRequest::findOrFail($id);
            $updated = $this->feeWaiverService->handleFeeWaiver($feeWaiver, $request->validated()['action']);

            return $this->successResponse("Fee waiver request {$updated->status} successfully.", $updated);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}