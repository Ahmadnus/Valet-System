<?php

namespace App\Http\Controllers;

use App\Http\Requests\FindNearestLocationRequest;
use App\Http\Resources\Location\LocationResource;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function findNearest(FindNearestLocationRequest $request)
    {
        $userLat = $request->latitude;
        $userLng = $request->longitude;

        if (!$userLat || !$userLng) {
            return response()->json(['message' => 'Latitude and longitude are required'], 400);
        }

        $locations = Location::selectRaw("
        id, name, latitude, longitude,
        (6371 * acos(
            cos(radians(?)) * cos(radians(latitude)) *
            cos(radians(longitude) - radians(?)) +
            sin(radians(?)) * sin(radians(latitude))
        )) AS distance",
        [$userLat, $userLng, $userLat]
    )
    ->orderBy("distance", "asc") // Order by nearest
    ->get();

    if ($locations->isEmpty()) {
        return response()->json(['message' => 'No locations found'], 404);
    }

    return response()->json([
        'nearest_locations' => LocationResource::collection($locations)
    ]);
}
}
