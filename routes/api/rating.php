<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RatingController;
use App\Http\Middleware\lang;


Route::prefix('ratings')
    ->middleware(['auth:sanctum', Lang::class])
    ->controller(RatingController::class)
    ->group(function () {
        Route::post('agent/{request_id}', 'rateAgent')
            ->middleware('check.role:valet_customer');

        Route::post('customer/{request_id}', 'rateCustomer')
            ->middleware('check.role:valet_agent');
    });