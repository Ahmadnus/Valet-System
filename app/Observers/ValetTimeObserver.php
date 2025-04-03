<?php

namespace App\Observers;


use App\Models\Request as UserRequest;
use App\Models\ValetTime;
use App\Models\Location;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
class ValetTimeObserver
{
    /**
     * Handle the ValetTime "created" event.
     */


    /**
     * Handle the ValetTime "updated" event.
     */
    public function updated(UserRequest $request)
    {
        // Ensure the request has required timestamps
        if (!$request->completed_time || !$request->in_progress_time) {
            Log::warning("Cannot calculate valet time: Missing timestamps for request_id: {$request->id}");
            return;
        }

        // Fetch the location to determine cost
        $location = Location::find($request->location_id);

        if (!$location) {
            Log::warning("Cannot calculate valet time: Location not found for request_id: {$request->id}");
            return;
        }

        // Fetch total hours
        $totalHours = $request->total_hours ?? 0;

        if ($totalHours <= 0) {
            Log::warning("Cannot calculate valet time: Invalid total_hours for request_id: {$request->id}");
            return;
        }

        // Calculate price
        $price = $totalHours * $location->cost;

        // Create ValetTime record
        ValetTime::create([
            'request_id' => $request->id,
            'user_id' => $request->user_id,
            'hours' => $totalHours,
            'price' => $price,

        ]);

        Log::info("Valet time recorded for user_id: {$request->user_id}, request_id: {$request->id}");
    }


    /**
     * Handle the ValetTime "deleted" event.
     */
    public function deleted(ValetTime $valetTime): void
    {
        //
    }

    /**
     * Handle the ValetTime "restored" event.
     */
    public function restored(ValetTime $valetTime): void
    {
        //
    }

    /**
     * Handle the ValetTime "force deleted" event.
     */
    public function forceDeleted(ValetTime $valetTime): void
    {
        //
    }
}
