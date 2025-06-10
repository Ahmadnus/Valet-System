<?php

namespace Database\Seeders;
use Database\Seeders\RolesAndPermissions;
use Database\Seeders\CustomerSeeder;
use Database\Seeders\AgentSeeder;
use Database\Seeders\LocationSeeder;
use Database\Seeders\ManagerSeeder;
use Database\Seeders\HostSeeder;
use Database\Seeders\CartypeSeeder;
use Database\Seeders\CarSeeder;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();


            $this->call([

                RolesAndPermissions::class,
                AgentSeeder::class,
                HostSeeder::class,
                CustomerSeeder::class,
                ManagerSeeder::class,

                CartypeSeeder::class,
                CarSeeder::class,
                LocationSeeder::class,

                // Add any others here
            ]);

    }
}
