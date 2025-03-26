<?php

namespace App\Services\Payments;

use App\Models\Location;
use App\Models\Request as UserRequest;
use App\Services\Payment\PaymentInterface;
use Illuminate\Support\Facades\Auth;
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
        $valetTime = $request->valetTime->first();

        $hours = $valetTime->hours;
        $locationid=UserRequest::where('user_id', Auth::id())->first();
        $cost=Location::where('id',$locationid->location_id)->with("requests")->first();

        $totalPrice = $hours * $cost->cost;


        $amountInCents = $totalPrice * 100;

        \Stripe\Stripe::setApiKey(config('services.stripe.secret_key'));

        try {
            $checkoutSession = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => $amountInCents, // Dynamically calculated amount
                        'product_data' => [
                            'name' => 'Valet Parking Service',
                            'description' => "{$hours} hours of valet service",
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => url('/success'),
                'cancel_url' => url('/cancel'),
            ]);

            return response()->json(['url' => $checkoutSession->url], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}