<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\Cartype;
use App\Models\Customer;
use App\Models\Host;
use App\Models\Manager;
use App\Models\Provider;
use App\Models\User;
use Spatie\Permission\Models\Role;
        use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cartypeId = Cartype::inRandomOrder()->value('id');

        for ($userId = 78; $userId <= 113; $userId++) {
            // Ensure user exists
            $user = User::find($userId);
            if (!$user) continue;

            // Create a random plate number
            $plateNumber = strtoupper(Str::random(3)) . '-' . rand(1000, 9999);

            Car::create([
                'user_id' => $user->id,
                'cartypeable_id' => $cartypeId,
                'cartypeable_type' => \App\Models\CarType::class,
                'plate_number' => $plateNumber,
            ]);

        }

    }
}
