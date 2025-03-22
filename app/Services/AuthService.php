<?php

namespace App\Services;

use App\Http\Resources\UserResource;
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
use SomarKesen\TelegramGateway\Facades\TelegramGateway;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;


class AuthService
{
    public function register(array $data)
    {
        try {
            DB::beginTransaction();


            $client = Customer::create([

            ]);


            $userData = Arr::only($data, ['name', 'password', 'phone_number']);
            $user = $client->user()->create($userData);


            $role = Role::firstOrCreate(['name' => 'valet_customer']);
            $user->assignRole($role);





            $otp = mt_rand(10000, 99999);
            Cache::put("otp_{$user->phone_number}", $otp, now()->addMinutes(5));


            TelegramGateway::sendVerificationMessage($user->phone_number, [
                'code' => $otp,
                'ttl' => 300,


            ]);

            DB::commit();

            return [
                'user' => $user,

            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
    public function verifyOtp($phoneNumber, $otp)
    {
        dd();
        $cachedOtp = Cache::get("otp_{$phoneNumber}");

        if (!$cachedOtp) {
            throw new Exception('OTP expired or invalid.');
        }

        if ($cachedOtp != $otp) {
            throw new Exception('Invalid OTP.');
        }


        Cache::forget("otp_{$phoneNumber}");

        return [
            'message' => 'OTP verified successfully!',
        ];
    }


    public function login($data)
    {
        $user = User::where('phone_number', $data['phone_number'])->first();

        if (!Hash::check($data['password'], $user->password)) {
            throw new Exception('invalid phone or password');
        }
        $otp = mt_rand(10000, 99999);
            Cache::put("otp_{$user->phone_number}", $otp, now()->addMinutes(5));


            TelegramGateway::sendVerificationMessage($user->phone_number, [
                'code' => $otp,
                'ttl' => 300,


            ]);

    }
    public function verifyLogOtp($phoneNumber, $otp)
{

    $cachedOtp = Cache::get("otp_{$phoneNumber}");

    if (!$cachedOtp) {
        throw new Exception('OTP expired or invalid.');
    }

    if ($cachedOtp != $otp) {
        throw new Exception('Invalid OTP.');
    }

    // Find user by phone number
    $user = User::where('phone_number', $phoneNumber)->first();
    if (!$user) {
        throw new Exception('User not found.');
    }


    $user->update(['is_verified' => true]);


    $token = $user->createToken('auth_token')->plainTextToken;


    Cache::forget("otp_{$phoneNumber}");

    return ([
        'message' => 'OTP verified successfully!',
        'token' => $token,
        'user' => new UserResource($user),
    ]);
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
