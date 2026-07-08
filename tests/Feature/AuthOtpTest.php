<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use SomarKesen\TelegramGateway\Facades\TelegramGateway;
use Tests\TestCase;

class AuthOtpTest extends TestCase
{
    use RefreshDatabase;

    private function makeCustomer(string $phone = '+963912345678', string $password = 'secret123'): User
    {
        $customer = Customer::create([]);

        return User::create([
            'name' => 'Test User',
            'phone_number' => $phone,
            'password' => bcrypt($password),
            'userable_id' => $customer->id,
            'userable_type' => Customer::class,
        ]);
    }

    public function test_login_does_not_issue_a_token_before_otp_is_verified(): void
    {
        TelegramGateway::shouldReceive('sendVerificationMessage')->once()->andReturnNull();
        $user = $this->makeCustomer();

        $response = $this->postJson('/api/auth/login', [
            'phone_number' => '+963912345678',
            'password' => 'secret123',
        ]);

        $response->assertOk();
        $this->assertNull($response->json('data.token'));
        $this->assertSame(0, $user->fresh()->tokens()->count());
    }

    public function test_otp_verification_issues_a_token(): void
    {
        TelegramGateway::shouldReceive('sendVerificationMessage')->andReturnNull();
        $user = $this->makeCustomer();

        $this->postJson('/api/auth/login', [
            'phone_number' => '+963912345678',
            'password' => 'secret123',
        ])->assertOk();

        $otp = Cache::get('otp_+963912345678');
        $this->assertNotNull($otp);

        $response = $this->postJson('/api/auth/verifylog', [
            'phone_number' => '+963912345678',
            'otp' => $otp,
        ]);

        $response->assertOk();
        $this->assertNotEmpty($response->json('data.token'));
        $this->assertSame(1, $user->fresh()->tokens()->count());
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->makeCustomer();

        $this->postJson('/api/auth/login', [
            'phone_number' => '+963912345678',
            'password' => 'wrong-password',
        ])->assertStatus(401);
    }
}
