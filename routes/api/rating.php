<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RatingController;
use App\Http\Middleware\lang;


Route::middleware('auth:sanctum')->group(function () {


Route::post('/rate-agent/{id}', [RatingController::class, 'rateAgent'])
    ->middleware('check.role:valet_customer');


Route::post('/rate-customer/{id}', [RatingController::class, 'rateCustomer'])
    ->middleware('check.role:valet_agent');

});
