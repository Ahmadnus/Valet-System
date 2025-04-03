<?php

namespace App\Http\Controllers;
use App\Models\Request as UserRequest;

use App\Services\Payments\PaymentService;
use App\Models\Payment;
use App\Models\User;
use App\Models\LoyaltyPoint;
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

        // Update payment status (Observer will handle history & loyalty points)
        $payment->update(['status' => 'completed']);

        return response()->json(['message' => 'Payment successful']);
    }
    public function showPaymentHistory()
    {
        $user = Auth::user();

        $paymentHistory = PaymentHistory::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'payment_history' => $paymentHistory
        ]);
    }
}