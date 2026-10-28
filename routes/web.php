<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ahmad;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RequestController as AdminRequestController;
use App\Http\Controllers\Admin\AgentController as AdminAgentController;
use App\Http\Controllers\Admin\LocationController as AdminLocationController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification ;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:web')->group(function () {
        Route::get('login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AdminAuthController::class, 'login'])->name('login.attempt');
    });

    Route::middleware('admin')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::prefix('requests')->name('requests.')->group(function () {
            Route::get('/', [AdminRequestController::class, 'index'])->name('index');
            Route::get('{id}', [AdminRequestController::class, 'show'])->name('show');
            Route::post('{id}/assign', [AdminRequestController::class, 'assign'])->name('assign');
            Route::post('{id}/cancel', [AdminRequestController::class, 'cancel'])->name('cancel');
        });

        Route::prefix('agents')->name('agents.')->group(function () {
            Route::get('/', [AdminAgentController::class, 'index'])->name('index');
            Route::post('{id}/assign', [AdminAgentController::class, 'assign'])->name('assign');
            Route::post('{id}/unassign', [AdminAgentController::class, 'unassign'])->name('unassign');
            Route::post('{id}/status', [AdminAgentController::class, 'updateStatus'])->name('status');
        });

        Route::prefix('locations')->name('locations.')->group(function () {
            Route::get('/', [AdminLocationController::class, 'index'])->name('index');
            Route::post('/', [AdminLocationController::class, 'store'])->name('store');
            Route::put('{id}', [AdminLocationController::class, 'update'])->name('update');
            Route::delete('{id}', [AdminLocationController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [AdminPaymentController::class, 'index'])->name('index');
            Route::post('{id}/status', [AdminPaymentController::class, 'updateStatus'])->name('status');
        });
    });
});
Route::get('/ee', function () {
    return view('firbase');
});




Route::get('test', function () {

     $stripeSecretKey = config('services.stripe.secret_key');

    // \Stripe\Stripe::setApiKey($stripeSecretKey);
    // // header('Content-Type: application/json');

    $YOUR_DOMAIN = config('app.url');

    // $checkout_session = \Stripe\Checkout\Session::create([
    //     'line_items' => [
    //         [
    //             'price_data' => [
    //                 'currency' => 'usd',
    //                 'unit_amount' => 2000,
    //                 'product_data' => [
    //                     'name' => 'T-shirt',
    //                     'description' => 'Comfortable cotton t-shirt',
    //                     'images' => ['https://beeban.qa/storage/1439/Screenshot-2025-01-24-190552.png'],
    //                 ],
    //             ],
    //             'quantity' => 1,
    //         ],

    //         [
    //             'price_data' => [
    //                 'currency' => 'usd',
    //                 'unit_amount' => 4000,
    //                 'product_data' => [
    //                     'name' => 'test product',
    //                     'description' => 'lorem ipsum set',
    //                     'images' => ['https://beeban.qa/storage/1439/Screenshot-2025-01-24-190552.png'],
    //                 ],
    //             ],
    //             'quantity' => 2,
    //         ],

    //     ],
    //     'mode' => 'payment',
    //     'success_url' => $YOUR_DOMAIN . '/success',
    //     'cancel_url' => $YOUR_DOMAIN . '/cancel',
    // ]);
    // return $checkout_session->url;
    // header("HTTP/1.1 303 See Other");
    // header("Location: " . $checkout_session->url);
});

Route::get('success', function () {
    return 'success';
});
Route::view('fcm','firbase');
Route::get('tst',function(){

//    $messaging = app('firebase.messaging');
//    $message = CloudMessage::new()
    //    ->withNotification(Notification::create('Title', 'Body'))
    //    ->withData(['key' => 'value'])
    //    ->toToken('...')
/// ->toTopic('...')
        // ->toCondition('...')
    ;

    //$result = $messaging->subscribeToTopic('customer', $registrationTokenOrTokens);
    //$messaging->send($message);
});


