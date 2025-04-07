<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Services\LoyaltyPointService;
use App\Http\Requests\LoyaltyPointsRequest;
use App\Http\Resources\InvoiceResource;
use App\app\Traits\ApiResponse;


class LoyaltyPointController extends Controller
{
    use ApiResponse;

    protected $loyaltyPointService;

    public function __construct(LoyaltyPointService $loyaltyPointService)
    {
        $this->loyaltyPointService = $loyaltyPointService;
    }

    /**
     * Check user loyalty points
     */
    public function checkLoyaltyPoints()
    {
        try {
            $points = $this->loyaltyPointService->checkLoyaltyPoints();

            return $this->successResponse('Loyalty points retrieved successfully', ['points' => $points]);
        } catch (Exception $e) {
            Log::error("Check Loyalty Points Error: " . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Use loyalty points
     */
    public function useLoyaltyPoints(LoyaltyPointsRequest $request,$request_id)
    {
        try {
            $points = $request->validated()['points'];

            $invoice = $this->loyaltyPointService->useLoyaltyPoints($points,$request_id);

            return $this->successResponse('Points applied successfully', [
                'invoice' => new InvoiceResource($invoice),
            ]);
        } catch (Exception $e) {
            Log::error("Use Loyalty Points Error: " . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}