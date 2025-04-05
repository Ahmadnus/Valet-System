<?php

namespace App\Http\Controllers;

use App\app\Traits\ApiResponse;
use App\Models\Rating;
use App\Http\Requests\StoreRatingRequest;
use App\Http\Requests\UpdateRatingRequest;
use App\Http\Resources\Rating\RatingResource;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use App\Services\RatingService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class RatingController extends Controller
{
    use ApiResponse;

    public function __construct(protected RatingService $ratingService) {}

    public function rateAgent(StoreRatingRequest $request, $agentId)
    {
        try {
            $rating = $this->ratingService->rateAgent($request->validated(), $agentId);
            return $this->successResponse('Rating submitted successfully', new RatingResource($rating), 201);
        } catch (\Exception $e) {
            Log::error("Rate Agent Error: " . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    public function rateCustomer(StoreRatingRequest $request, $customerId)
    {
        try {
            $rating = $this->ratingService->rateCustomer($request->validated(), $customerId);
            return $this->successResponse('Rating submitted successfully', new RatingResource($rating), 201);
        } catch (\Exception $e) {
            Log::error("Rate Customer Error: " . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }
}