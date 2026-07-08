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

use App\Notifications\Notification;
use Kreait\Firebase\Messaging\Notification as MessagingNotification;

class AuthService
{
    public function register(array $data)
    {
        try {
            DB::beginTransaction();

            $existingUser = User::where('phone_number', $data['phone_number'])->first();

            if ($existingUser && $existingUser->hasRole('valet_guest')) {
                $client = Customer::create([]);
                $existingUser->update([
                    'name' => $data['name'],
                    'password' => bcrypt($data['password']),
                    'userable_id' => $client->id,
                    'userable_type' => Customer::class,
                ]);


                $existingUser->removeRole('valet_guest');
                $role = Role::firstOrCreate(['name' => 'valet_customer']);
                $existingUser->assignRole($role);

                $user = $existingUser;

            } else {

                $client = Customer::create([]);

                $userData = Arr::only($data, ['name', 'password', 'phone_number']);
                $userData['password'] = bcrypt($userData['password']);

                $user = $client->user()->create($userData);

                $role = Role::firstOrCreate(['name' => 'valet_customer']);
                $user->assignRole($role);
            }


            $otp = random_int(10000, 99999);
            Cache::put("otp_{$user->phone_number}", $otp, now()->addMinutes(5));
            TelegramGateway::sendVerificationMessage($user->phone_number, [
                'code' => $otp,
                'ttl' => 300,
            ]);


            if (!empty($data['fcm_token'])) {
                $this->storeFcmToken($user, $data['fcm_token']);
            }

            DB::commit();

            return [
                'user' => $user,

            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    private function storeFcmToken(User $user, string $token): void
    {
        if (!$user->fcmTokens()->where('token', $token)->exists()) {
            $user->fcmTokens()->create([
                'token' => $token,
            ]);
        }
    }



    public function login($data)
    {
        $user = User::where('phone_number', $data['phone_number'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw new \Illuminate\Auth\AuthenticationException('Invalid phone number or password.');
        }
        $otp = random_int(10000, 99999);
            Cache::put("otp_{$user->phone_number}", $otp, now()->addMinutes(5));

            TelegramGateway::sendVerificationMessage($user->phone_number, [
                'code' => $otp,
                'ttl' => 300,
            ]);
            if(isset($data['fcm_token'])){
                $this->storeFcmToken($user,$data['fcm_token']);
            }

            // No access token is issued here: the token is only granted after
            // the OTP is verified in verifyOtpCode(). Returning a token before
            // verification would make the OTP step meaningless (2FA bypass).
            return ([
                'message' => 'OTP sent successfully. Please verify to continue.',
                'user' => new UserResource($user),
            ]);


    }
    public function verifyOtpCode(string $phoneNumber, string|int $otp): array
{
    $cachedOtp = Cache::get("otp_{$phoneNumber}");

    if (!$cachedOtp) {
        throw new \Exception('OTP expired or invalid.');
    }

    if (!hash_equals((string) $cachedOtp, (string) $otp)) {
        throw new \Exception('Invalid OTP.');
    }

    $user = User::where('phone_number', $phoneNumber)->first();
    if (!$user) {
        throw new \Exception('User not found.');
    }

    // إزالة OTP من الكاش بعد التحقق
    Cache::forget("otp_{$phoneNumber}");

    // The access token is issued here — only after a successful OTP check —
    // so the OTP step actually gates authentication.
    $token = $user->createToken('login')->plainTextToken;

    return [
        'message' => 'OTP verified successfully!',
        'user' => new \App\Http\Resources\UserResource($user),
        'token' => $token,
    ];
}


    private function createAccessToken($user)
    {
        $token = $user->createToken("login")->plainTextToken;
        return $token;
    }
    private function storFcm($user,$tokens){
        $user->fcmTokens()->create([
            'tokens'=>$tokens
        ]);

    }
    public function logout(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            throw new \Exception('No authenticated user.');
        }

        $token = $user->currentAccessToken();

        if (!$token) {
            throw new \Exception('User already logged out or token not found.');
        }


        $token->delete();


        $user->fcmTokens()->delete();

        return ['message' => 'Logged out successfully.'];
    }
}
