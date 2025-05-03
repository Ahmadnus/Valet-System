<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\Request as UserRequest;
use App\Models\User;
use App\Models\LoyaltyPoint;
use App\Models\PaymentHistory;
use App\Services\Payments\PaymentService;

use App\Services\Payments\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentObserver
{
    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment)
    {
        if ($payment->wasChanged('status') && $payment->status === 'completed') {
            Log::info("Processing payment completion for session_id: {$payment->stripe_session_id}");

            // Retrieve the associated user request
            $userRequest = UserRequest::find($payment->request_id);
            $user = $userRequest?->user;


            PaymentHistory::create([
                'user_id' => $user->id,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);


            $loyaltyPoints = LoyaltyPoint::firstOrCreate(['user_id' => $user->id]);
            $loyaltyPoints->points += $payment->amount / 100;
            $loyaltyPoints->save();

            Log::info("Payment history and loyalty points updated for user_id: {$user->id}");


        }
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "restored" event.
     */
    public function restored(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "force deleted" event.
     */
    public function forceDeleted(Payment $payment): void
    {
        //
    }
}
