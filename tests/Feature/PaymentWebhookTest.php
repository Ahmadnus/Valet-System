<?php

namespace Tests\Feature;

use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.stripe.webhook_secret' => 'whsec_test_secret']);
    }

    public function test_webhook_rejects_request_without_signature(): void
    {
        $this->postJson('/api/payments/webhook', ['type' => 'checkout.session.completed'])
            ->assertStatus(400);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $this->call(
            'POST',
            '/api/payments/webhook',
            [],
            [],
            [],
            ['HTTP_STRIPE_SIGNATURE' => 't=1,v1=deadbeef'],
            json_encode(['type' => 'checkout.session.completed'])
        )->assertStatus(400);
    }

    public function test_payment_success_landing_is_read_only(): void
    {
        // The public landing endpoint must never confirm a payment; with no
        // matching session it simply reports an unknown status and 200.
        $this->getJson('/api/payments/success?session_id=cs_does_not_exist')
            ->assertOk()
            ->assertJson(['data' => ['status' => 'unknown']]);

        $this->assertSame(0, Payment::where('status', 'completed')->count());
    }
}
