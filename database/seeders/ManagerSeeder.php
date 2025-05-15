<?php

namespace Database\Seeders;
use App\Models\Agent;
use App\Models\Customer;
use App\Models\Guest;
use App\Models\Host;
use App\Models\Manager;
use App\Models\Provider;
use Spatie\Permission\Models\Role;
        use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class ManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create role if it doesn't exist
        $role = Role::firstOrCreate(['name' => 'valet_guest']);

        // Create guest userable model
        $guest = Guest::create([]);

        // Create user record
        $user = User::create([
            'name' => 'Guest User',
            'phone_number' => '+963900000000',
            'password' => Hash::make('password'),
            'userable_id' => $guest->id,
            'userable_type' => Guest::class,
        ]);

        // Assign valet_guest role
        $user->assignRole($role);
        for ($i = 1; $i <= 10; $i++) {
    $guest = Guest::create([]);

    $user = User::create([
        'name' => "Guest User $i",
        'phone_number' => '+9639000110' . $i,
        'password' => Hash::make('password'),
        'userable_id' => $guest->id,
        'userable_type' => Guest::class,
    ]);

    $user->assignRole($role);
}
    }
}
