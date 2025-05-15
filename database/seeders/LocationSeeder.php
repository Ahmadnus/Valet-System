<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\ParkingSlot;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Factories\CarFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    $hostIds = range(46, 55); // 10 hosts
    $agentIds = range(6, 25); // 20 agents
    $locations = [];

    // Create one location per host
    foreach ($hostIds as $index => $hostId) {
        DB::beginTransaction();

        try {
            $location = Location::create([
                'latitude' => 24.7136 + mt_rand(-1000, 1000) / 10000,
                'longitude' => 46.6753 + mt_rand(-1000, 1000) / 10000,
                'cost' => rand(10, 50),
                'host_id' => $hostId,
                'ar' => ['name' => "موقع " . ($index + 1)],
                'en' => ['name' => "Location " . ($index + 1)],
            ]);

            $locations[] = $location;

            // Create 3–6 parking slots
            $slotsCount = rand(3, 6);
            for ($j = 1; $j <= $slotsCount; $j++) {
                ParkingSlot::create([
                    'location_id' => $location->id,
                    'slot_number' => "SLOT-$j",
                    'status' => 'available',
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to create location for host $hostId: " . $e->getMessage());
        }
    }

    // Assign each agent to a random location (no repetition per agent)
    foreach ($agentIds as $agentId) {
        $location = collect($locations)->random();

        DB::table('location_agent')->insert([
            'agent_id' => $agentId,
            'location_id' => $location->id,
        ]);

        Log::info("Assigned agent $agentId to location {$location->id}");
    }
}
}
