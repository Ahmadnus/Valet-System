<?php

namespace App\Services\Requests;

class TradParkingService
{
    /**
     * Create a new class instance.
     */
    public function parkGuestCar(Request $request)
{
    $request->validate([
        'phone' => 'required|string',
        'plate_number' => 'required|string',
        'location_id' => 'required|integer',
        'parking_slot_id' => 'required|integer',
    ]);

    return DB::transaction(function () use ($request) {
        $agentId = Auth::id();

        // Find or create valet_customer user (even if he is a guest logically)
        $user = User::firstOrCreate(
            ['phone' => $request->phone],
            [
                'role' => 'valet_customer',
                'name' => null,
                'email' => null,
            ]
        );

        // Create or get car
        $car = Car::firstOrCreate(
            ['plate_number' => $request->plate_number],
            ['user_id' => $user->id]
        );

        // Create parking request
        $parkingRequest = Request::create([
            'user_id' => $user->id,
            'car_id' => $car->id,
            'valet_agent_id' => $agentId,
            'location_id' => $request->location_id,
            'parking_slot_id' => $request->parking_slot_id,
            'status' => Request::STATUS_IN_PROGRESS,
            'in_progress_time' => now(),
        ]);

        // Update agent status
        LocationAgent::where('agent_id', $agentId)
            ->where('location_id', $request->location_id)
            ->update(['status' => 'occupied']);

        return response()->json([
            'message' => 'Guest parking request created and started successfully.',
            'request_id' => $parkingRequest->id,
        ]);
    });
}
}
