<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarController;
use App\Http\Middleware\lang;

Route::prefix('cars')
    ->middleware(['auth:sanctum', 'check.type:Customer', Lang::class])
    ->controller(CarController::class)
    ->group(function () {
        Route::get('/', 'index');                  // GET /cars
        Route::get('/{car}', 'show');              // GET /cars/{car}
        Route::post('/', 'store');                 // POST /cars
        Route::put('/{car}', 'update');            // PUT /cars/{car}
        Route::delete('/{car}', 'destroy');        // DELETE /cars/{car}
    });