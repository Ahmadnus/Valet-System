<?php

namespace App\Services\Payments;

use App\app\traits\CheckRequestStatus;
use App\Models\Location;
use App\Models\Request as UserRequest;
use App\Models\ValetTime;
use App\Services\Payment\PaymentInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StripeService implements PaymentsInterface
{
    use CheckRequestStatus;
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    public function pay(UserRequest $userRequest)
{
    info('Processing valet payment...');


    $this->checkRequestStatusInPreparing($userRequest->id);


    $valetTime = $userRequest->valetTime->where('user_id', Auth::id())->first();
    if (!$valetTime) {
        return response()->json(['error' => 'No valid valet time found'], 404);
    }

    $hours = $valetTime->hours ?? 1;

    $location = Location::find($userRequest->location_id);
    if (!$location) {
        return response()->json(['error' => 'Location not found'], 404);
    }

    $amountInCents = $valetTime->price * 100;


    \Stripe\Stripe::setApiKey(config('services.stripe.secret_key'));

    $checkoutSession = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'unit_amount' => $amountInCents,
                'product_data' => [
                    'name' => 'Valet Parking Service',
                    'description' => "{$hours} hours of valet service",
                ],
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        //'success_url' => url('payment-success?session_id={CHECKOUT_SESSION_ID}'),
        'success_url' => url('/success'),
        'cancel_url' => url('/cancel'),

    ]);

    Log::info('Stripe Checkout Session Created:', ['session_id' => $checkoutSession->id]);


    $userRequest->payment()->update([
        'amount' => $amountInCents,
        'stripe_session_id' => $checkoutSession->id,
    ]);

    Log::info('Payment record updated:', ['stripe_session_id' => $checkoutSession->id]);

    return response()->json([
        'url' => $checkoutSession->url,
        'session_id' => $checkoutSession->id,
    ], 200);
}}
