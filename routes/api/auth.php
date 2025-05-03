<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\lang;

Route::prefix('auth')->group(function () {
    Route::post('signup', [AuthController::class, "signup"]);
    Route::post('verify', [AuthController::class, 'verifyOtp']);
    Route::post('verifylog', [AuthController::class, 'verifyLogOtp']);
    Route::post('signupAdmin', [AuthController::class, "siginUpAd"]);
    Route::post('login', [AuthController::class, "login"]);
    Route::post('loginAdmin', [AuthController::class, "loginAd"]);
    Route::post('logout', [AuthController::class, 'logout']);
});