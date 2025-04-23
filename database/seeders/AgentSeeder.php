<?php

namespace Database\Seeders;
use App\Models\Agent;
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

class AgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $agent = Agent::create(); // Empty agent record

            $user = User::create([
                'name' => "Agent User $i",
                'phone_number' => '+96393787586' . $i,
                'password' => Hash::make('password'),
                'userable_id' => $agent->id,
                'userable_type' => Agent::class,
            ]);

            $user->assignRole('valet_agent');
        }
    }
}
