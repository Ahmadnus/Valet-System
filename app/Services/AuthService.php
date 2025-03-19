<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Models\Client;
use App\Models\Customer;
use App\Models\Provider;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AuthService
{
    public function register( array $data)
    {
        try {
            DB::beginTransaction();

            // Create Customer
            $client = Customer::create([
                // Add necessary customer attributes if needed
            ]);

            // Ensure only user-related fields are passed
            $userData = Arr::only($data, ['name', 'email', 'password','phone_number']);
            $user = $client->user()->create($userData);

            // Assign 'valet_customer' role
            $role = Role::firstOrCreate(['name' => 'valet_customer']);
            $user->assignRole($role);

            // Create Access Token
            $token = $this->createAccessToken($user);

            DB::commit();

            return [
                'user' => $user,
                'token' => $token
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
    public function login($data)
    {
        $user = User::where('email', $data['email'])->first();

        if (!Hash::check($data['password'], $user->password)) {
            throw new Exception('invalid phone or password');
        }
        $token = $this->createAccessToken($user);
        $data = [
            'user' => $user->load('userable'),
            'token' => $token
        ];
        return $data;
    }
    private function createAccessToken($user)
    {
        $token = $user->createToken("login")->plainTextToken;
        return $token;
    }
    public function logout() {
        $user = Auth:: user();
        $user->$this->currentAccessToken()->delete();
        return true;
    }
}
