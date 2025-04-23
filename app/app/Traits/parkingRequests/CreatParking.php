<?php

namespace App\app\Traits\parkingRequests;

use App\Models\Car;
use App\Models\ParkingSlot;
use App\Models\LocationAgent;
use App\Models\Request;
use Illuminate\Support\Facades\Log;
trait CreatParking
{
    protected function checkCarOwnership(int $userId, int $carId): void
    {
        $ownsCar = Car::where('id', $carId)->where('user_id', $userId)->exists();

        if (!$ownsCar) {
            Log::warning("User $userId attempted to use car $carId they do not own.");
            throw new \Exception(trans("You do not own the selected car."));
        }
    }

    protected function checkExistingRequest(int $userId, int $carId): void
    {
        $existing = Request::where('user_id', $userId)
            ->where('car_id', $carId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->exists();

        if ($existing) {
            Log::info("User $userId already has an existing request for car $carId.");
            throw new \Exception(trans("You already have a pending or in-progress request for this car."));
        }
    }

    protected function validateCarOwnership(int $userId, int $carId): void
    {
        $this->checkCarOwnership($userId, $carId);
    }

    protected function ensureNoPendingOrActiveRequest(int $userId, int $carId): void
    {
        $this->checkExistingRequest($userId, $carId);
    }

    protected function validateParkingSlot(int $slotId, int $locationId): void
    {
        $slot = ParkingSlot::where('id', $slotId)
            ->where('location_id', $locationId)
            ->first();

        if (!$slot) {
            Log::warning("Invalid parking slot $slotId for location $locationId.");
            throw new \Exception(trans("The selected parking slot does not belong to this location."));
        }

        if ($slot->status === 'occupied') {
            Log::info("Parking slot $slotId is currently occupied.");
            throw new \Exception(trans("This parking slot is currently occupied. Please select another slot."));
        }
    }

    protected function ensureAvailableAgentExists(int $locationId): void
    {
        $available = LocationAgent::where('location_id', $locationId)
            ->where('status', 'available')
            ->exists();

        if (!$available) {
            Log::info("No available agents for location $locationId.");
            throw new \Exception(trans('Sorry, there are no available agents at this location.'));
        }
    }
}
