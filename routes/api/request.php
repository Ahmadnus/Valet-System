<?php

use App\Http\Middleware\lang;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\TradParkingController;



Route::middleware(['auth:sanctum', 'check.role:valet_customer',lang::class])->group(function () {
    Route::post('/parking-request', [RequestController::class, 'createParkingRequest']);
    Route::put('/parking-requests/{id}/prepare', [RequestController::class, 'requestCarPreparation']);
    Route::post('/find-nearest', [LocationController::class, 'findNearest'])->middleware(lang::class);
    Route::get('/find-parking/{id}', [LocationController::class, 'show']);


});
//valet_agent
Route::middleware(['auth:sanctum', 'check.role:valet_agent',lang::class])->group(function () {
    Route::put('/parking-requests/{id}/accept', [RequestController::class, 'acceptParkingRequest']);
    Route::post('/requests/{request}/start', [RequestController::class, 'startParkingRequest']);
    Route::post('/parking-requests/{id}/complete', [RequestController::class, 'completeParkingRequest']);
    Route::post('parking-requests/{requestId}/delivery', [RequestController::class, 'acceptDeliveryRequest']);
    Route::post('/cancel-parking-request/{requestId}', [RequestController::class, 'cancelParkingRequest']);
    Route::post('/parking-requests', [TradParkingController::class, 'parkGuestCar']);
    Route::post('/guest/valet/pay/{id}', [TradParkingController::class, 'createPayment']);
    Route::post('/guest/valet/complete/{id}', [TradParkingController::class, 'completeGuestValet']);




});