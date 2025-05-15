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
    public function paymentSuccess(Request $request)
    {
        Log::info('Payment success request received', ['request' => $request->all()]);

        $sessionId = $request->query('session_id') ?? Payment::where('status', 'pending')->latest()->value('stripe_session_id');

        if (!$sessionId) {
            return response()->json(['error' => 'Missing session ID and no pending payments found'], 400);
        }

        $payment = Payment::where('stripe_session_id', $sessionId)->first();

        if (!$payment) {
            return response()->json(['error' => 'Payment record not found'], 404);
        }


        $payment->update(['status' => 'completed']);

        return response()->json(['message' => 'Payment successful']);
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