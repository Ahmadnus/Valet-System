<?php

namespace Database\Seeders;
use App\Models\Provider;
use Spatie\Permission\Models\Role;
        use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Customer;
use App\Models\Host;
use App\Models\Manager;





class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    for ($i = 1; $i <= 20; $i++) {
        // Create empty valet customer record
        $customer = Customer::create();

        // Create associated user
        $user = User::create([
            'name' => "Customer $i",
            'phone_number' => '+9639123422' . str_pad($i, 2, '0', STR_PAD_LEFT),
            'password' => Hash::make('password'),
            'userable_id' => $customer->id,
            'userable_type' => Customer::class,
        ]);

        // Assign valet_customer role
        $user->assignRole('valet_customer');
    }
}
}
