<?php

namespace App\Services\Requests;

use App\Models\Request;
use App\Models\Car;
use Illuminate\Support\Facades\Auth;
use Exception;

class ParkingRequestService
{
    public function createParkingRequest(array $data)
    {
        $userId = Auth::id();


        $ownsCar = Car::where('id', $data['car_id'])
            ->where('user_id', $userId)
            ->exists();

        if (!$ownsCar) {
            throw new Exception('You can only request parking for a car you own.');
        }


        $existingRequest = Request::where('user_id', $userId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->exists();

        if ($existingRequest) {
            throw new Exception('You have already sent a request. Please wait until it is completed.');
        }


        return Request::create([
            'user_id' => $userId,
            'car_id' => $data['car_id'],
            'location_id' => $data['location_id'],
            'status' => 'pending',
            'request_time' => now(),
        ]);
    }

    public function acceptParkingRequest(int $requestId)
    {
        $valetAgentId = Auth::id();

        if (!Auth::user()->hasRole('valet_agent')) {
            throw new Exception('Unauthorized. Only valet agents can accept requests.');
        }

        $parkingRequest = Request::where('id', $requestId)
            ->where('status', 'pending')
            ->first();

        if (!$parkingRequest) {
            throw new Exception('Request not found or already accepted.');
        }


        $parkingRequest->update([
            'status' => Request::STATUS_IN_PROGRESS,
            'valet_agent_id' => $valetAgentId,
            'in_progress_time' => now(),
        ]);

        return $parkingRequest;
    }

    public function requestCarPreparation(int $requestId)
    {
        $parkingRequest = Request::where('id', $requestId)
            ->where('status', Request::STATUS_IN_PROGRESS)
            ->first();

        if (!$parkingRequest) {
            throw new Exception('Request not found or not in progress.');
        }

        $parkingRequest->update([
            'status' => Request::STATUS_PREPARING,

        ]);

        return $parkingRequest;
    }

    public function completeParkingRequest(int $requestId, string $plateNumber)
    {
        $valetAgentId = Auth::id();

        if (!Auth::user()->hasRole('valet_agent')) {
            throw new Exception('Unauthorized. Only valet agents can complete requests.');
        }

        $parkingRequest = Request::where('id', $requestId)
            ->where('status', Request::STATUS_PREPARING)
            ->first();

        if (!$parkingRequest) {
            throw new Exception('Request not found or not ready for completion.');
        }


        $car = Car::where('id', $parkingRequest->car_id)
            ->where('plate_number', $plateNumber)
            ->first();

        if (!$car) {
            throw new Exception('Plate number does not match the registered car.');
        }


        $parkingRequest->update([
            'status' => Request::STATUS_COMPLETED,
            'completed_time' => now(),
        ]);

        return $parkingRequest;
    }
}