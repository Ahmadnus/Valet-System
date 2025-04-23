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
        $agentIds = [2, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15];

        for ($i = 1; $i <= 6; $i++) {
            DB::beginTransaction();

            try {
                $location = Location::create([
                    'latitude' => 24.7136 + mt_rand(-1000, 1000) / 10000, // Random small offset
                    'longitude' => 46.6753 + mt_rand(-1000, 1000) / 10000,
                    'cost' => rand(10, 50),
                    'host_id' => 1, // Change if you use multiple hosts
                    'ar' => ['name' => "موقع $i"],
                    'en' => ['name' => "Location $i"],
                ]);

                // Create random 3–6 parking slots
                $slotsCount = rand(3, 6);
                for ($j = 1; $j <= $slotsCount; $j++) {
                    $slotNumber = "SLOT-$j";

                    ParkingSlot::create([
                        'location_id' => $location->id,
                        'slot_number' => $slotNumber,
                        'status' => 'available',
                    ]);
                }


                $assignedAgents = collect($agentIds)->random(rand(3, 5))->all();
                $location->agents()->attach($assignedAgents);

                DB::commit();

                Log::info("Seeded location $i with {$slotsCount} slots and agents: " . implode(', ', $assignedAgents));
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Failed to seed location $i: " . $e->getMessage());
            }
        }
    }

}
