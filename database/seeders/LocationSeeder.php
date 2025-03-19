<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Factories\CarFactory;
class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Location::create(['name' => 'Mall Entrance', 'latitude' => 24.7136, 'longitude' => 46.6753]);
        //Location::create(['name' => 'Hotel Parking', 'latitude' => 25.276987, 'longitude' => 55.296249]);
        //Location::create(['name' => 'Business Center', 'latitude' => 40.7128, 'longitude' => -74.0060]);
    }
}
