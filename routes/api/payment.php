

<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Middleware\lang;

Route::prefix('payments')->controller(PaymentController::class)->group(function () {
    Route::get('stripe/{userRequest}', 'createStripePayment')->middleware('auth:sanctum');


    Route::get('payment-success', [PaymentController::class, 'paymentSuccess']);
    Route::get('/payment-history', [PaymentController::class, 'showPaymentHistory'])->middleware('auth:sanctum');
Route::get('/mypayment', [PaymentController::class, 'showPay'])->middleware('auth:sanctum');

});
