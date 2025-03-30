<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ahmad;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification ;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/ee', function () {
    return view('findAgent');
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


