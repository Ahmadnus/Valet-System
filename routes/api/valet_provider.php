



<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartypeController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\TradParkingController;
use App\Http\Middleware\lang;
Route::middleware(['auth:sanctum', 'check.role:valet_provider',lang::class])->group(function () {



Route::get('cartypes', [CartypeController::class, 'index']);

Route::post('cartypes', [CartypeController::class, 'store']);

Route::get('cartypes/{cartype}', [CartypeController::class, 'show']);


Route::put('cartypes/{cartype}', [CartypeController::class, 'update']);


Route::delete('cartypes/{cartype}', [CartypeController::class, 'destroy']);
    });
    Route::middleware(['auth:sanctum', 'check.role:valet_provider',lang::class])->group(function () {
        Route::get('/role-counts', [ManagerController::class, 'getUserCountsByRole']);
        Route::get('/users', [ManagerController::class, 'index']);
        Route::get('/users/{id}', [ManagerController::class, 'show']);
        Route::post('/users', [ManagerController::class, 'store']);
        Route::put('/users/{user}', [ManagerController::class, 'update']);
        Route::delete('/users/{id}', [ManagerController::class, 'destroy']);
        Route::get('/earnings/today/locations', [TradParkingController::class, 'getTodayCashEarningsPerLocation']);
        Route::get('/earnings/today/total', [TradParkingController::class, 'getTodayTotalCash']);




    });
    Route::middleware(['auth:sanctum','check.role:valet_provider'])->group(function () {
        Route::post('/locations', [LocationController::class, 'addLocation']);
        Route::put('/locations/{id}', [LocationController::class, 'update']);
        Route::delete('/locations/{id}', [LocationController::class, 'destroy']);
        Route::get('/locations/{id}', [LocationController::class, 'show']);
        Route::get('/locations', [LocationController::class, 'index']);

        Route::get('/ratings/customers', [RatingController::class, 'getAllCustomerRatings']);


        Route::get('/ratings/agents', [RatingController::class, 'getAllAgentRatings']);

        });
