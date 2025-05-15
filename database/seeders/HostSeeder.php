<?php

namespace Database\Seeders;
use App\Models\Customer;
use App\Models\Host;
use App\Models\Manager;
use App\Models\Provider;
use Spatie\Permission\Models\Role;
        use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class HostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i <= 9; $i++) {
            // Create empty valet_host record
            $host = Host::create();

            // Create associated user
            $user = User::create([
                'name' => "Valet Host $i",
                'phone_number' => '+96390123123' . $i, // Saudi-like dummy numbers
                'password' => Hash::make('password'),
                'userable_id' => $host->id,
                'userable_type' => Host::class,
            ]);

            // Assign valet_host role
            $user->assignRole('valet_host');
        }
    }
}
