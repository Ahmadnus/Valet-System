<?php

use App\Http\Middleware\lang;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\TradParkingController;

Route::middleware(['auth:sanctum', 'check.type:Customer', Lang::class])
    ->group(function () {
        Route::prefix('parking-requests')
            ->controller(RequestController::class)
            ->group(function () {
                Route::post('/', 'createParkingRequest');
                Route::put('{userRequest}/prepare', 'requestCarPreparation');
            });

        Route::prefix('locations')
            ->controller(LocationController::class)
            ->group(function () {
                Route::post('/find-nearest', 'findNearest');
                Route::get('/find-parking/{location}', 'show');
            });
    });

// Agent group
Route::middleware(['auth:sanctum', 'check.type:Agent', Lang::class])
    ->group(function () {
        Route::prefix('guest/valet')
            ->controller(TradParkingController::class)
            ->group(function () {
                Route::post('/parking-requests', 'parkGuestCar');
                Route::post('/pay/{userRequest}', 'createPayment');
                Route::post('/complete/{userRequest}', 'completeGuestValet');
            });
    });

// ✅ Shared route: Agent + Customer

Route::middleware(['auth:sanctum', 'check.user.allowed', Lang::class])
    ->put('parking-requests/{userRequest}/status', [RequestController::class, 'updateStatus']);
