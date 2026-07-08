<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\lang;

Route::prefix('auth')->group(function () {
    Route::post('signup', [AuthController::class, "signup"])->middleware('throttle:6,1');
    Route::post('verify', [AuthController::class, 'verifyOtp'])->middleware('throttle:6,1');
    Route::post('verifylog', [AuthController::class, 'verifyLogOtp'])->middleware('throttle:6,1');

    Route::post('login', [AuthController::class, "login"])->middleware('throttle:6,1');

    Route::post('logout', [AuthController::class, 'logout']);
});