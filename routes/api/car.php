<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarController;
use App\Http\Middleware\lang;

Route::middleware(['auth:sanctum', 'check.role:valet_customer',lang::class])->group(function () {
    Route::get('/cars', [CarController::class, 'index']);
    Route::get('/car/{car}', [CarController::class, 'show']);
    Route::post('cars', [CarController::class, 'store'])->middleware('auth:api');
    Route::put('/cars/{car}', [CarController::class, 'update']);
    Route::delete('/cars/{car}', [CarController::class, 'destroy']);
});
