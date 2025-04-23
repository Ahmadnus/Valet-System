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
        try {$lang = app()->getLocale();
            $rating = $this->ratingService->rateAgent($request->validated(), (int) $agentId);
            return $this->successResponse(trans('Rating submitted successfully'), new RatingResource($rating), 201);
        } catch (\Exception $e) {
            Log::error("Rate Agent Error: " . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    public function rateCustomer(StoreRatingRequest $request, $customerId)
    {
        try {
            $lang=app()->getlocale();

            $rating = $this->ratingService->rateCustomer($request->validated(), (int) $customerId);

            return $this->successResponse(trans('Rating submitted successfully'), new RatingResource($rating), 201);
        } catch (\Exception $e) {
            Log::error("Rate Customer Error: " . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }


    public function getAllAgentRatings()
    {
        $ratings = $this->ratingService->getAllAgentRatings();
        return RatingResource::collection($ratings);
    }

    public function getAllCustomerRatings()
    {
        $ratings = $this->ratingService->getAllCustomerRatings();
        return RatingResource::collection($ratings);
    }
}