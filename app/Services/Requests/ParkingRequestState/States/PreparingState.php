<?php

namespace App\Services\Requests\ParkingRequestState\States;
use App\Events\CarPreparationRequested;
use App\Models\Request;
use App\Services\Requests\ParkingRequestState\ParkingRequestStateInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
class PreparingState implements ParkingRequestStateInterface
{
    public function handle(Request $request, array $data = [])
    {
        $user = Auth::user();

        if (!$user->hasRole('valet_customer')) {
            Log::warning("User {$user->id} attempted to prepare request {$request->id} without valet_customer role.");
            throw new \Exception(trans('Unauthorized: You must be a valet customer to prepare this request.'));
        }

        $userId = $user->id;

        if ($request->user_id !== $userId) {
            Log::warning("User $userId tried to prepare request {$request->id} but is not the owner.");
            throw new \Exception(trans("You are not allowed to prepare this request."));
        }

        $latitude = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;

        if (is_null($latitude) || is_null($longitude)) {
            throw new \Exception(trans("Latitude and longitude are required."));
        }

        if (!app('App\Services\Requests\ParkingRequestService')->isUserWithinDistance($latitude, $longitude)) {
            throw new \Exception(trans('You are too far from the nearest location. Please get closer.'));
        }

        $request->update([
            'status' => Request::STATUS_PREPARING,
            'completed_time' => now(),
            'customer_latitude' => $latitude,
            'customer_longitude' => $longitude,
        ]);

        Log::info("User $userId requested car preparation for request {$request->id}.");

        event(new CarPreparationRequested($request, $latitude, $longitude));

        return $request;
    }
}