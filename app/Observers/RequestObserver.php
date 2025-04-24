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
    if (
        $request->isDirty('status') &&
        (
            ($request->status === \App\Models\Request::STATUS_IN_PROGRESS && $request->requested_by_user === 1) ||
            $request->status === \App\Models\Request::STATUS_ACCEPTED
        )
    ) {
        $request->parkingSlot()->update([
            'status' => 'occupied'
        ]);

        Log::info("ParkingSlot {$request->parking_slot_id} marked as occupied via observer.");
    }

    if ($request->isDirty('status') && $request->status === \App\Models\Request::STATUS_COMPLETED) {
        $request->parkingSlot()->update([
            'status' => 'available'
        ]);

        Log::info("ParkingSlot {$request->parking_slot_id} marked as available via observer.");
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
