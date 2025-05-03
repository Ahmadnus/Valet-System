<?php

namespace App\Traits\app\Traits;


use App\Models\Location;

trait CheckDistanceTrait
{
    /**
     * Check if the user is within a maximum distance from the nearest location.
     *
     * @param  float  $userLat
     * @param  float  $userLng
     * @param  int    $maxDistanceKm
     * @return bool
     */
    public function isUserWithinDistance($userLat, $userLng, $maxDistanceKm = 10)
    {
        $nearestLocation = Location::selectRaw("
            id, latitude, longitude,
            (6371 * acos(
                cos(radians(?)) * cos(radians(latitude)) *
                cos(radians(longitude) - radians(?)) +
                sin(radians(?)) * sin(radians(latitude))
            )) AS distance",
            [$userLat, $userLng, $userLat]
        )
        ->orderBy('distance', 'asc')
        ->first();

        if ($nearestLocation && $nearestLocation->distance > $maxDistanceKm) {
            return false;
        }

        return true;
    }
}
