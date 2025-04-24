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
        if ($request->status === \App\Models\Request::STATUS_PREPARING || $request->status === \App\Models\Request::STATUS_IN_PROGRESS
        && $request->requested_by_user === 1) {

            // Safety check for timestamps


            // 🕒 Calculate time difference
            $timeDiff = abs(now()->diffInHours($request->in_progress_time));
            $request->total_hours = $timeDiff;
            $request->saveQuietly(); // to avoid infinite observer loop

            $location = Location::find($request->location_id);
            if (!$location) {
                Log::warning("Location not found for request ID: {$request->id}");
                return;
            }

            $price = $timeDiff * $location->cost;

            ValetTime::updateOrCreate(
                ['request_id' => $request->id],
                [
                    'user_id' => $request->user_id,
                    'hours' => $timeDiff,
                    'price' => $price,
                ]
            );

            Log::info("ValetTime calculated & saved for Request ID: {$request->id}, User ID: {$request->user_id}");
        }
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
