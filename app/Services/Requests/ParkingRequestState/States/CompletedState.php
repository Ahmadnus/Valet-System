<?php

namespace App\Services\Requests\ParkingRequestState\States;

use App\Models\Car;
use App\Models\LocationAgent;
use App\Models\Request;
use App\Services\Requests\ParkingRequestState\ParkingRequestStateInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
class CompletedState implements ParkingRequestStateInterface
{
    public function handle(Request $request, array $data = [])
    {
        $user = Auth::user();

        if (!$user->hasRole('valet_agent')) {
            Log::warning("User {$user->id} attempted to complete request {$request->id} without valet_agent role.");
            throw new \Exception(trans('Unauthorized: You must be a valet agent to complete this request.'));
        }

        $agentId = $user->id;

        if ($request->status !== Request::STATUS_DELIVERING) {
            Log::warning("Request {$request->id} is not in delivering status.");
            throw new \Exception(trans('Request is not in delivering status.'));
        }

        $plateNumber = $data['plate_number'] ?? null;
        if (!$plateNumber) {
            throw new \Exception(trans('Plate number is required.'));
        }

        $car = Car::where('plate_number', $plateNumber)->first();

        if (!$car) {
            Log::warning("Plate number $plateNumber not found.");
            throw new \Exception(trans('The plate number does not exist.'));
        }

        if ($request->car_id !== $car->id) {
            Log::warning("Mismatch between car in request ({$request->car_id}) and provided plate.");
            throw new \Exception(trans('The plate number does not match the car in this request.'));
        }

        $request->update([
            'status' => Request::STATUS_COMPLETED,
        ]);

        LocationAgent::where('agent_id', $agentId)
            ->where('location_id', $request->location_id)
            ->update(['status' => 'available']);

        Log::info("Request {$request->id} completed successfully by agent $agentId.");

        return $request;

    }
}