<?php
namespace App\Services;

use App\Http\Resources\Location\LocationResource;
use App\Models\Location;
use App\Models\ParkingSlot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\FindNearestLocationRequest;
use App\Http\Requests\Location\LocationRequest;
use App\Http\Requests\Location\LocationRequestLocationRequest;

class LocationService
{
    public function findNearestLocation(FindNearestLocationRequest $request)
    {
        $userLat = $request->latitude;
        $userLng = $request->longitude;

        if (!$userLat || !$userLng) {
            throw new \Exception('Latitude and longitude are required');
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
        ->orderBy("distance", "asc")
        ->with('parkingSlots')
        ->get();

        if ($locations->isEmpty()) {
            throw new \Exception('No locations found');
        }

        return $locations;
    }

    public function addLocation(LocationRequest $request)
    {
        DB::beginTransaction();

        // Check if location name already exists
        if (Location::where('host_id', $request->host_id)->exists()) {
            Log::warning("Attempted to create duplicate location: {$request->name}");
            throw new \Exception("Location with this name already exists.");
        }

        $location = Location::create([
            'name' => $request->name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'host_id' => $request->host_id,
            'cost' => $request->cost,
        ]);

        Log::info("Location created: {$location->name}");

        if ($request->has('number_of_slots') && is_numeric($request->number_of_slots) && $request->number_of_slots > 0) {
            for ($i = 1; $i <= $request->number_of_slots; $i++) {
                $slotNumber = "SLOT-$i";

                // Check if slot number already exists for this location
                if (ParkingSlot::where('location_id', $location->id)->where('slot_number', $slotNumber)->exists()) {
                    Log::error("Duplicate slot detected: $slotNumber for location {$location->id}");
                    throw new \Exception("Slot number $slotNumber already exists for this location.");
                }

                ParkingSlot::create([
                    'location_id' => $location->id,
                    'slot_number' => $slotNumber,
                    'status' => 'available',
                ]);
            }
        }

        if ($request->has('agent_ids') && is_array($request->agent_ids)) {
            $location->agents()->attach($request->agent_ids);
        }

        DB::commit();

        return $location->load('parkingSlots', 'agents');
    }



        public function updateLocation(LocationRequest $request, $id)
        {
            DB::beginTransaction();

            $location = Location::findOrFail($id);

            $location->update([
                'name' => $request->name,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'host_id' => $request->host_id,
                'cost' => $request->cost,
            ]);

            Log::info("Location updated: {$location->id}");

            $location->agents()->sync($request->agent_ids);

            DB::commit();

            return $location;
        }

        public function deleteLocation($id)
        {
            DB::beginTransaction();

            $location = Location::findOrFail($id);

            $location->agents()->detach();

            $location->delete();

            Log::info("Location deleted: {$location->id}");

            DB::commit();
        }

        public function getLocation($id)
        {
            return Location::with([
                'agents',
                'parkingSlots' => function ($query) {
                    $query->where('status', 'available');
                }
            ])->findOrFail($id);
        }

        public function getAllLocations()
        {
            return Location::with('agents')->get();
        }
    }


