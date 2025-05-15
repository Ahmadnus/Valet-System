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

class RolesAndPermissions extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


     $roles = [
        'valet_customer' => Role::firstOrCreate(['name' => 'valet_customer']),
        'valet_agent' => Role::firstOrCreate(['name' => 'valet_agent']),
        'valet_host' => Role::firstOrCreate(['name' => 'valet_host']),
        'valet_provider' => Role::firstOrCreate(['name' => 'valet_provider']),
        'platform_manager' => Role::firstOrCreate(['name' => 'platform_manager']),
    ];


    $users = [
        [
            'name' => 'Customer User',

            'phone_number' => '+963942345678',
            'password' => Hash::make('password'),
            'role' => 'valet_customer',
            'userable' => Customer::create([]),
        ],
        [
            'name' => 'Agent User',

            'phone_number' => '+963932345678',
            'password' => Hash::make('password'),
            'role' => 'valet_agent',
            'userable' => Agent::create([]),
        ],
        [
            'name' => 'Host User',
            'email' => 'host@example.com',
            'phone_number' => '+963922345678',
            'password' => Hash::make('password'),
            'role' => 'valet_host',
            'userable' => Host::create([]),
        ],
        [
            'name' => 'Provider User',

            'phone_number' => '+963912345678',
            'password' => Hash::make('password'),
            'role' => 'valet_provider',
            'userable' => Provider::create([]),
        ],
        [
            'name' => 'Manager User',

            'phone_number' => '+963939115126',
            'password' => Hash::make('password'),
            'role' => 'platform_manager',
            'userable' => Manager::create([]),
        ],
    ];


    foreach ($users as $userData) {

        $user = User::create([
            'name' => $userData['name'],

            'phone_number' => $userData['phone_number'],
            'password' => $userData['password'],
            'userable_id' => $userData['userable']->id,
            'userable_type' => get_class($userData['userable']),
        ]);

        // Assign role to user
        $user->assignRole($roles[$userData['role']]);
    }

                // Create and Assign Permissions (Manually)

                // Valet Customer Permissions
                /*$valetCustomer->givePermissionTo([
                    Permission::firstOrCreate(['name' => 'add_car']),
                    Permission::firstOrCreate(['name' => 'edit_car']),
                    Permission::firstOrCreate(['name' => 'delete_car']),
                    Permission::firstOrCreate(['name' => 'view_car']),
                    Permission::firstOrCreate(['name' => 'track_order']),
                    Permission::firstOrCreate(['name' => 'request_car']),
                    Permission::firstOrCreate(['name' => 'chat_valet_agent']),
                    Permission::firstOrCreate(['name' => 'call_valet_agent']),
                    Permission::firstOrCreate(['name' => 'view_request_history']),
                    Permission::firstOrCreate(['name' => 'receive_notifications']),
                    Permission::firstOrCreate(['name' => 'rate_valet_provider']),
                    Permission::firstOrCreate(['name' => 'make_payment']),
                    Permission::firstOrCreate(['name' => 'request_fee_waiver']),
                    Permission::firstOrCreate(['name' => 'view_loyalty_points']),
                    Permission::firstOrCreate(['name' => 'redeem_loyalty_points']),
                    Permission::firstOrCreate(['name' => 'find_nearby_valet']),
                ]);

                // Valet Agent Permissions
                $valetAgent->givePermissionTo([
                    Permission::firstOrCreate(['name' => 'create_valet_request']),
                    Permission::firstOrCreate(['name' => 'add_car_details']),
                    Permission::firstOrCreate(['name' => 'scan_code']),
                    Permission::firstOrCreate(['name' => 'rate_valet_customer']),
                    Permission::firstOrCreate(['name' => 'view_valet_capacity']),
                ]);

                // Valet Host Permissions
                $valetHost->givePermissionTo([
                    Permission::firstOrCreate(['name' => 'manage_dashboard']),
                    Permission::firstOrCreate(['name' => 'link_with_valet_provider']),
                    Permission::firstOrCreate(['name' => 'export_ratings']),
                    Permission::firstOrCreate(['name' => 'receive_host_notifications']),
                    Permission::firstOrCreate(['name' => 'manage_subscriptions']),
                    Permission::firstOrCreate(['name' => 'waive_fees']),
                ]);

                // Valet Provider Permissions
                $valetProvider->givePermissionTo([
                    Permission::firstOrCreate(['name' => 'manage_provider_dashboard']),
                    Permission::firstOrCreate(['name' => 'link_provider_with_host']),
                    Permission::firstOrCreate(['name' => 'export_provider_ratings']),
                    Permission::firstOrCreate(['name' => 'manage_provider_locations']),
                    Permission::firstOrCreate(['name' => 'manage_provider_subscriptions']),
                ]);

                // Platform Manager Permissions
                $platformManager->givePermissionTo([
                    Permission::firstOrCreate(['name' => 'manage_platform_dashboard']),
                    Permission::firstOrCreate(['name' => 'control_users']),
                    Permission::firstOrCreate(['name' => 'control_requests']),
                    Permission::firstOrCreate(['name' => 'send_notifications']),
                    Permission::firstOrCreate(['name' => 'resolve_complaints']),
                    Permission::firstOrCreate(['name' => 'generate_reports']),
                    Permission::firstOrCreate(['name' => 'manage_content']),
                    Permission::firstOrCreate(['name' => 'manage_roles']),
                ]);
                */
            }
        }








