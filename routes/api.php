<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\CartypeController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FeeWaiverController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ValetTimeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\NotificationController;
use App\Http\Middleware\CheakUser;
use App\Http\model\Payment;
use App\Models\Payment as ModelsPayment;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\TruckungController;
use App\Http\Controllers\LoyaltyPointController;
use Illuminate\Support\Facades\Broadcast;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::prefix('auth')->group(function () {


  Route::post('signup', [AuthController::class, "signup"])->name('signup');
  Route::post('/verify', [AuthController::class, 'verifyOtp'])->name('otp.verify');
  Route::post('/verifylog', [AuthController::class, 'verifyLogOtp'])->name('otp.verifyy');
  Route::post('signupAdmin', [AuthController::class, "siginUpAd"]);
  Route::post('login', [AuthController::class, "login"]);
  Route::post('loginAdmin', [AuthController::class, "loginAd"]);
  Route::post('logout/{id}', [AuthController::class, "logout"])->name('logout');
});
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cars', [CarController::class, 'index']); // Get user cars
    Route::post('/cars', [CarController::class, 'store']); // Create car
    Route::put('/cars/{car}', [CarController::class, 'update']); // Update car
    Route::delete('/cars/{car}', [CarController::class, 'destroy']);
});


Route::get('/cartypes', [CartypeController::class, 'index']); // Get all car types
Route::post('/cartypes', [CartypeController::class, 'store']); // Create a new car type
Route::get('/cartypes/{cartype}', [CartypeController::class, 'show']); // Get a specific car type
Route::put('/cartypes/{cartype}', [CartypeController::class, 'update']); // Update a car type
Route::delete('/cartypes/{cartype}', [CartypeController::class, 'destroy']); // Delete a car type

Route::post('/requests', [RequestController::class, 'store']);
Route::post('/send-request', [RequestController::class, 'sendRequest'])->middleware('auth:sanctum');
Route::post('/rate/{id}', [RatingController::class, 'store'])->middleware('auth:sanctum');


Route::post('/find-nearest', [LocationController::class, 'findNearest']);
Route::get('/find-parking/{id}', [LocationController::class, 'show']);






/////manger






Route::middleware(['auth:sanctum', 'platform_manager'])->group(function () {
    Route::get('/role-counts', [ManagerController::class, 'getUserCountsByRole']);
    Route::get('/users', [ManagerController::class, 'index']);
    Route::get('/users/{id}', [ManagerController::class, 'show']);
    Route::post('/users', [ManagerController::class, 'store']);
    Route::put('/users/{user}', [ManagerController::class, 'update']);
    Route::delete('/users/{id}', [ManagerController::class, 'destroy']);

});


Route::middleware(['auth:sanctum'])->group(function () {
Route::post('/locations', [LocationController::class, 'addLocation']);
Route::put('/locations/{id}', [LocationController::class, 'update']);
Route::delete('/locations/{id}', [LocationController::class, 'destroy']);
Route::get('/locations/{id}', [LocationController::class, 'show']);
Route::get('/locations', [LocationController::class, 'index']);
});

//

    Route::post('/parking-requests', [RequestController::class, 'createParkingRequest']);
    Route::put('/parking-requests/{id}/accept', [RequestController::class, 'acceptParkingRequest']);
    Route::post('/parking-requests/{id}/prepare', [RequestController::class, 'requestCarPreparation']);
    Route::post('/parking-requests/{id}/complete', [RequestController::class, 'completeParkingRequest']);
    Route::post('/cal/{req}', [ValetTimeController::class, 'parkingTime']);



Route::prefix('payments')->controller(PaymentController::class)->group(function () {
    Route::get('stripe/{userRequest}', 'createStripePayment')->middleware('auth:sanctum');
    Route::post('fatora', 'createFatora')->middleware('auth:sanctum');


});
Route::get('payment-success', [PaymentController::class, 'paymentSuccess']);






Route::post('/send-notification', [NotificationController::class, 'send']);




Route::middleware(['auth:sanctum'])->group(function () {

Route::post('/start-chat', [ChatController::class, 'startChat']);
Route::post('/send-message', [ChatController::class, 'sendMessage']);
Route::get('/chat-messages/{chat_id}', [ChatController::class, 'getMessages']);

});



//Broadcast::routes();
Route::post('/update-location', [TruckungController::class, 'updateLocation']);
Route::get('/get-location/{agent_id}', [TruckungController::class, 'getLocation']);



////

Route::get('/loyalty-points', [LoyaltyPointController::class, 'show']);
Route::post('/use-loyalty-points', [LoyaltyPointController::class, 'useLoyaltyPoints']);
Route::post('/earn-loyalty-points', [LoyaltyPointController::class, 'earnLoyaltyPoints']);
Route::post('/generate-invoice', [LoyaltyPointController::class, 'generateInvoice']);
Route::middleware('auth:sanctum')->get('/cheak-points', [LoyaltyPointController::class, 'checkLoyaltyPoints']);


/////
Route::post('/fee-waiver/request', [FeeWaiverController::class, 'requestFeeWaiver']);
Route::post('/fee-waiver/{id}', [FeeWaiverController::class, 'handleFeeWaiver']);




///
Route::get('/payment-history', [PaymentController::class, 'showPaymentHistory'])->middleware('auth:sanctum');