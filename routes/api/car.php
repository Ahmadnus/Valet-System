<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarController;
use App\Http\Controllers\CartypeController;
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

// Read-only car type reference data, needed by any authenticated user when
// registering a car. Deliberately a distinct path from /cartypes (the
// Provider-only CRUD group also registers GET /cartypes; Laravel resolves
// duplicate method+URI pairs to whichever route was registered last, so
// reusing that path here would silently get shadowed by the Provider route).
Route::get('/car-types', [CartypeController::class, 'index'])
    ->middleware(['auth:sanctum', Lang::class]);