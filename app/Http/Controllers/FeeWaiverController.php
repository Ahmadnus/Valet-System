<?php

namespace App\Http\Controllers;

use App\app\Traits\ApiResponse;
use App\Http\Requests\AnserFeeWaiverRequest;
use App\Models\FeeWaiverRequest;
use App\Models\Request as UserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RequestFeeWaiverRequest;
use App\Http\Requests\HandleFeeWaiverRequest;
use App\Http\Resources\FeeWaiverResource;
use App\Services\FeeWaiverService;

class FeeWaiverController extends Controller
{
    use ApiResponse;

    protected FeeWaiverService $feeWaiverService;

    public function __construct(FeeWaiverService $feeWaiverService)
    {
        $this->feeWaiverService = $feeWaiverService;
    }

    public function requestFeeWaiver(HandleFeeWaiverRequest $request)
    {
        $lang = app()->getLocale();

        try {
            $requestId = $request->route('requestId'); // الحصول على ID من Route

            $feeWaiver = $this->feeWaiverService->requestFeeWaiver($requestId);

            return $this->successResponse(
                trans('Fee waiver request sent.'),
                new FeeWaiverResource($feeWaiver)
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function handleFeeWaiver(AnserFeeWaiverRequest $request, $feeWaiver )
    {   $lang = app()->getLocale();
        try {
            $feeWaiver = FeeWaiverRequest::findOrFail($feeWaiver );
            $updated = $this->feeWaiverService->handleFeeWaiver($feeWaiver, $request->validated()['action']);

            return $this->successResponse(
                " {$updated->status}",
                new FeeWaiverResource($updated)
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        } }}

