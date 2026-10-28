<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\ParkingSlot;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $locations = Location::with(['host'])
            ->withCount([
                'parkingSlots',
                'parkingSlots as occupied_slots_count' => fn ($q) => $q->where('status', 'occupied'),
                'agents',
            ])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $hosts = User::role('valet_host')->get();
        $assignedHostIds = Location::whereNotNull('host_id')->pluck('host_id');
        $availableHosts = $hosts->whereNotIn('id', $assignedHostIds)->values();

        return view('admin.locations.index', compact('locations', 'hosts', 'availableHosts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'cost' => ['required', 'numeric', 'min:0'],
            'host_id' => ['required', 'integer', 'exists:users,id'],
            'number_of_slots' => ['nullable', 'integer', 'min:0', 'max:200'],
        ]);

        try {
            DB::transaction(function () use ($validated) {
                if (Location::where('host_id', $validated['host_id'])->exists()) {
                    throw new \Exception('This host already manages a location.');
                }

                $location = Location::create([
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'cost' => $validated['cost'],
                    'host_id' => $validated['host_id'],
                    'en' => ['name' => $validated['name_en']],
                    'ar' => ['name' => $validated['name_ar'] ?? $validated['name_en']],
                ]);

                for ($i = 1; $i <= ($validated['number_of_slots'] ?? 0); $i++) {
                    ParkingSlot::create([
                        'location_id' => $location->id,
                        'slot_number' => "SLOT-{$i}",
                        'status' => 'available',
                    ]);
                }

                Log::info("Admin created location {$location->id}.");
            });
        } catch (\Exception $e) {
            return back()->withErrors(['location' => $e->getMessage()])->withInput();
        }

        return redirect()->route('admin.locations.index')->with('status', 'Location created successfully.');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'cost' => ['required', 'numeric', 'min:0'],
            'host_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $location = Location::findOrFail($id);

        $location->update([
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'cost' => $validated['cost'],
            'host_id' => $validated['host_id'] ?? null,
            'en' => ['name' => $validated['name_en']],
            'ar' => ['name' => $validated['name_ar'] ?? $validated['name_en']],
        ]);

        Log::info("Admin updated location {$location->id}.");

        return redirect()->route('admin.locations.index')->with('status', 'Location updated successfully.');
    }

    public function destroy(int $id)
    {
        DB::transaction(function () use ($id) {
            $location = Location::findOrFail($id);
            $location->agents()->detach();
            $location->delete();
            Log::info("Admin deleted location {$id}.");
        });

        return back()->with('status', 'Location deleted.');
    }
}
