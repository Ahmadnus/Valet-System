<?php

namespace App\app\Traits;
use App\Models\Request;
use Exception;
use Illuminate\Support\Facades\Log;
trait HasRequestValidation
{
    protected function checkExistingRequest(int $userId, int $carId): void
    {
        $existingRequest = Request::where('user_id', $userId)
            ->where('car_id', $carId)
            ->whereIn('status', ['pending', 'in_progress', 'accepted', 'preparing'])
            ->exists();

        if ($existingRequest) {
            Log::info("User $userId attempted to create a duplicate parking request for car $carId.");
            throw new Exception(trans('You cannot submit a new request for the same car before completing the previous one.'));
        }
    }
}
