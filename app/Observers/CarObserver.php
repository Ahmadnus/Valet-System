<?php

namespace App\Observers;

use App\Models\Car;
use App\Models\Cartype;

class CarObserver
{
    /**
     * Handle the Car "created" event.
     */

        public function created(Car $car)
        {
            // Ensure the car has a related CarType
            if ($car->cartypeable && $car->cartypeable instanceof \App\Models\CarType) {
                // Check if CarType has a logo
                if ($car->cartypeable->hasMedia('logo')) {
                    $mediaItem = $car->cartypeable->getFirstMedia('logo');

                    if ($mediaItem) {
                        $car->addMedia($mediaItem->getPath()) // Get the logo path
                            ->preservingOriginal()
                            ->toMediaCollection('logo'); // Attach to Car's media
                    }
                }
            }
        }


    /**}
     *
     * Handle the Car "updated" event.
     */
    public function updated(Car $car): void
    {
        //
    }

    /**
     * Handle the Car "deleted" event.
     */
    public function deleted(Car $car): void
    {
        //
    }

    /**
     * Handle the Car "restored" event.
     */
    public function restored(Car $car): void
    {
        //
    }

    /**
     * Handle the Car "force deleted" event.
     */
    public function forceDeleted(Car $car): void
    {
        //
    }
}
