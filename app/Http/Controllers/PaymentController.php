<?php

namespace App\Http\Controllers;
use App\Models\Request as UserRequest;

use App\Services\Payments\PaymentService;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\User;
use App\Models\LoyaltyPoint;
use App\Models\ValetTime;
use App\Models\PaymentHistory;
use App\Services\Payments\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
   public function createStripePayment(UserRequest $userRequest)
    {
        $paymentService = new PaymentService(new StripeService());
        $url =  $paymentService->createPayment($userRequest);
        return response()->json(['url' => $url]);
    }
    /**
     * Browser landing page after Stripe Checkout. This is only informational
     * and MUST NOT change payment state — the redirect is fully attacker
     * controllable. Payments are confirmed exclusively via handleWebhook().
     */
    public function paymentSuccess(Request $request)
    {
        $sessionId = $request->query('session_id');
        $payment = $sessionId
            ? Payment::where('stripe_session_id', $sessionId)->first()
            : null;

        return response()->json([
            'message' => 'Thank you. Your payment is being processed.',
            'status' => $payment->status ?? 'unknown',
        ]);
    }

    /**
     * Stripe webhook endpoint. Verifies the Stripe signature and confirms the
     * payment on checkout.session.completed. This is the single source of
     * truth for marking a payment completed.
     */
    public function handleWebhook(Request $request)
    {
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                $secret
            );
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook: invalid payload', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook: signature verification failed');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $sessionId = $event->data->object->id;

            $payment = Payment::where('stripe_session_id', $sessionId)->first();

            if ($payment && $payment->status !== 'completed') {
                $payment->update(['status' => 'completed']); // idempotent
                Log::info('Payment confirmed via webhook', ['payment_id' => $payment->id]);
            }
        }

        return response()->json(['received' => true]);
    }
    public function showPaymentHistory()
    {
        $user = Auth::user();

        // Get the latest invoice entry
        $invoice = Invoice::where('user_id', $user->id)->latest()->first();

        // Fetch the price before discount if valet_time still exists
        $valetTime = ValetTime::where('user_id', $user->id)->first();

        return [
            'last_invoice' => $invoice,
            'used_loyalty_points' => $invoice && $invoice->points_used > 0,
            'points_used' => $invoice->points_used ?? 0,
            'total_amount' => $invoice->total_amount ?? ($valetTime->price ?? 0),
            'remaining_balance' => $invoice->remaining_balance ?? ($valetTime->price ?? 0),
        ];
    }

    public function showPay()
    {
        $user = Auth::user();

        // Get the latest valet time (payment)
        $payment = ValetTime::where('user_id', $user->id)->first();

        // Get latest invoice (if any)
        $invoice = Invoice::where('user_id', $user->id)->latest()->first();

        return [
            'payment' => $payment,
            'invoice' => $invoice,
            'used_loyalty_points' => $invoice && $invoice->points_used > 0,
            'points_used' => $invoice->points_used ?? 0,
            'remaining_balance' => $invoice->remaining_balance ?? ($payment->price ?? 0),
        ];
    }
}