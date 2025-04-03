<?php

namespace App\Services\Payments;

use App\Models\Location;
use App\Models\Request as UserRequest;
use App\Models\ValetTime;
use App\Services\Payment\PaymentInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StripeService implements PaymentsInterface
{
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

        // Fetch the user's valet request with related valet time
        $request = UserRequest::where('user_id', Auth::id())
            ->with('valetTime')
            ->first(); // Get the latest request

        if (!$request || !$request->valetTime) {
            return response()->json(['error' => 'No valid valet request found'], 404);
        }
        $valetTime = $userRequest->valetTime->where('user_id', Auth::id())->first();

        $Valetinfo=ValetTime::where('user_id', Auth::id())->first();

        $hours = $valetTime->hours??1;

        $locationid=UserRequest::where('user_id', Auth::id())->first();
        $cost=Location::where('id',$locationid->location_id)->with("requests")->first();

        $totalPrice = $hours * $cost->cost;

        $amountInCents = $valetTime->price*100;


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
                'success_url' => url('payment-success?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url' => url('/cancel'),
            ]);

            Log::info('Stripe Checkout Session Created:', ['session_id' => $checkoutSession->id]);

            // Save the payment
            $userRequest->payment()->update([
                'amount' => $amountInCents,


                'stripe_session_id' => $checkoutSession->id,
            ]);

            Log::info('Payment record created:', ['stripe_session_id' => $checkoutSession->id]);
            return response()->json(['url' => $checkoutSession->url, 'session_id' => $checkoutSession->id], 200);

}}