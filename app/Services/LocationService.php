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
use App\Http\Requests\UpdateLocationRequest;
use App\Models\User;

class LocationService
{
    public function findNearestLocation(FindNearestLocationRequest $request)
    {
        $lang=app()->getlocale();
        $userLat = $request->latitude;
        $userLng = $request->longitude;

        if (!$userLat || !$userLng) {
            throw new \Exception(trans('Latitude and longitude are required'));
        }

        $maxDistanceKm = 10;

$locations = Location::selectRaw("
    id, latitude, longitude,
    (6371 * acos(
        cos(radians(?)) * cos(radians(latitude)) *
        cos(radians(longitude) - radians(?)) +
        sin(radians(?)) * sin(radians(latitude))
    )) AS distance",
    [$userLat, $userLng, $userLat]
)
->having('distance', '<=', $maxDistanceKm)
->orderBy('distance', 'asc')
->with('parkingSlots')
->get();

        if ($locations->isEmpty()) {
            throw new \Exception('No locations found');
        }

        return LocationResource::collection($locations);
    }

    public function addLocation(LocationRequest $request)
{
    DB::beginTransaction();

    try {
        if (Location::where('host_id', $request->host_id)->exists()) {
            Log::warning("Attempted to create duplicate location: {$request->name}");
            throw new \Exception(trans("Location with this name already exists."));
        }

        $locationAt = [
            'latitude' => $request['latitude'],
            'longitude' => $request['longitude'],
            'cost' => $request['cost'],
            'host_id' => $request['host_id'],
            'ar' => ['name' => $request['name']['ar'] ?? ''],
            'en' => ['name' => $request['name']['en'] ?? ''],
        ];

        $location = Location::create($locationAt);

        Log::info("Location created: {$location->name}");

        if ($request->has('number_of_slots') && is_numeric($request->number_of_slots) && $request->number_of_slots > 0) {
            for ($i = 1; $i <= $request->number_of_slots; $i++) {
                $slotNumber = "SLOT-$i";

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
            $agents = User::whereIn('id', $request->agent_ids)->get();

            $alreadyAssignedAgents = $agents->filter(function ($agent) use ($location) {
                return $agent->locationse()
                    ->where('location_id', '!=', $location->id)
                    ->exists();
            });

            if ($alreadyAssignedAgents->isNotEmpty()) {
                $agentIdsStr = $alreadyAssignedAgents->pluck('id')->implode(', ');
                throw new \Exception("Some agents are already assigned to other locations: {$agentIdsStr}");
            }

            $location->agents()->syncWithoutDetaching($request->agent_ids);
        }

        DB::commit(); // ✅ أهم نقطة
        return $location->load('parkingSlots', 'agents');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Failed to create location: " . $e->getMessage());
        throw $e;
    }
}        public function updateLocation(UpdateLocationRequest $request, $id)
        {
            $location = Location::findOrFail($id);

            // تحديث بيانات الموقع
            $location->update([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'host_id' => $request->host_id,
                'cost' => $request->cost,
                'ar' => ['name' => $request->name['ar'] ?? ''],
                'en' => ['name' => $request->name['en'] ?? ''],
            ]);

            Log::info("Location updated: {$location->id}");

            // التحقق وربط الـ Agents
            if ($request->has('agent_ids') && is_array($request->agent_ids)) {
                $agents = User::whereIn('id', $request->agent_ids)->get();

                $alreadyAssignedAgents = $agents->filter(function ($agent) use ($location) {
                    return $agent->locationse()
                        ->where('location_id', '!=', $location->id)
                        ->exists();
                });

                if ($request->has('agent_ids') && is_array($request->agent_ids)) {
                    $agents = User::whereIn('id', $request->agent_ids)->get();

                    foreach ($agents as $agent) {
                        // إزالة جميع المواقع القديمة (يجب أن يكون له موقع واحد فقط)
                        $agent->locationse()->detach();
                    }

                    // ثم ربطهم بالموقع الجديد
                    $location->agents()->syncWithoutDetaching($request->agent_ids);
                }
                // sync بدلًا من syncWithoutDetaching لأنك في update
                $location->agents()->sync($request->agent_ids);
            }

            return $location->load('parkingSlots', 'agents');
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


