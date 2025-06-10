<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\ParkingSlot;
use App\Models\User;
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
        // Get hosts and agents dynamically
        $hostUsers = User::role('valet_host')->get();   // Using Spatie's role method
        $agentUsers = User::role('valet_agent')->get();

        $locations = [];

        foreach ($hostUsers as $index => $host) {
            DB::beginTransaction();

            try {
                $location = Location::create([
                    'latitude' => 24.7136 + mt_rand(-1000, 1000) / 10000,
                    'longitude' => 46.6753 + mt_rand(-1000, 1000) / 10000,
                    'cost' => rand(10, 50),
                    'host_id' => $host->id,
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
                Log::error("Failed to create location for host {$host->id}: " . $e->getMessage());
            }
        }

        // Assign each valet agent to a random location
        foreach ($agentUsers as $agent) {
            $location = collect($locations)->random();

            DB::table('location_agent')->insert([
                'agent_id' => $agent->id,
                'location_id' => $location->id,
            ]);

            Log::info("Assigned agent {$agent->id} to location {$location->id}");
        }
    }
}
