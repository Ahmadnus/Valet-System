<?php

namespace App\Observers;

use App\Models\Request;
use Illuminate\Support\Facades\Log;

class RequestObserver
{
    /**
     * Handle the Request "created" event.
     */
    public function created(Request $request): void
    {
        //
    }

    /**
     * Handle the Request "updated" event.
     */
    public function updated(Request $request)
    {
        if ($request->isDirty('status') && $request->status === Request::STATUS_ACCEPTED) {
            $request->parkingSlot()->update([
                'status' => 'occupied'
            ]);

            Log::info("ParkingSlot {$request->parking_slot_id} marked as occupied via observer.");
        }
    }

    /**
     * Handle the Request "deleted" event.
     */
    public function deleted(Request $request): void
    {
        //
    }

    /**
     * Handle the Request "restored" event.
     */
    public function restored(Request $request): void
    {
        //
    }

    /**
     * Handle the Request "force deleted" event.
     */
    public function forceDeleted(Request $request): void
    {
        //
    }
}
