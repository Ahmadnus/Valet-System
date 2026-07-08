<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Middleware\lang;

// Stripe calls this server-to-server; it is authenticated by the Stripe
// signature inside the controller, not by Sanctum.
Route::post('payments/webhook', [PaymentController::class, 'handleWebhook']);

// Browser landing after Checkout: no bearer token is present on the redirect,
// and it is purely informational (it never changes payment state).
Route::get('payments/success', [PaymentController::class, 'paymentSuccess']);

Route::prefix('payments')
    ->middleware(['auth:sanctum', 'check.type:Customer', Lang::class])
    ->controller(PaymentController::class)
    ->group(function () {
        Route::get('stripe/{userRequest}', 'createStripePayment');       // GET /payments/stripe/{userRequest}
        Route::get('history', 'showPaymentHistory');                     // GET /payments/history
        Route::get('latest', 'showLatestPayment');                       // GET /payments/latest
    });
