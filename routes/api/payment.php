

<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Middleware\lang;

Route::prefix('payments')
    ->middleware(['auth:sanctum', 'check.type:Customer', Lang::class])
    ->controller(PaymentController::class)
    ->group(function () {
        Route::get('stripe/{userRequest}', 'createStripePayment');       // GET /payments/stripe/{userRequest}
        Route::get('success', 'paymentSuccess');                         // GET /payments/success
        Route::get('history', 'showPaymentHistory');                     // GET /payments/history
        Route::get('latest', 'showLatestPayment');                       // GET /payments/latest
    });