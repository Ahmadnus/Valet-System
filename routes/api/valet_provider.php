



<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartypeController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\TradParkingController;
use App\Http\Middleware\lang;
Route::middleware(['auth:sanctum', 'check.type:Provider', Lang::class])->group(function () {

    // ========== Car Types ==========
    Route::prefix('cartypes')
        ->controller(CartypeController::class)
        ->group(function () {
            Route::get('/', 'index');              // GET /cartypes
            Route::post('/', 'store');             // POST /cartypes
            Route::get('/{cartype}', 'show');      // GET /cartypes/{cartype}
            Route::put('/{cartype}', 'update');    // PUT /cartypes/{cartype}
            Route::delete('/{cartype}', 'destroy');// DELETE /cartypes/{cartype}
        });

    // ========== Users (All Roles) ==========
    Route::prefix('users')
        ->controller(ManagerController::class)
        ->group(function () {
            Route::get('/', 'index');              // GET /users
            Route::post('/', 'store');             // POST /users
            Route::get('/{id}', 'show');           // GET /users/{id}
            Route::put('/{user}', 'update');       // PUT /users/{user}
            Route::delete('/{id}', 'destroy');     // DELETE /users/{id}
            Route::get('/role-counts', 'getUserCountsByRole'); // GET /users/role-counts
        });

    // ========== Earnings ==========
    Route::prefix('earnings')
        ->controller(TradParkingController::class)
        ->group(function () {
            Route::get('/today/locations', 'getTodayCashEarningsPerLocation'); // GET /earnings/today/locations
            Route::get('/today/total', 'getTodayTotalCash');                   // GET /earnings/today/total
        });

    // ========== Locations ==========
    Route::prefix('locations')
        ->controller(LocationController::class)
        ->group(function () {
            Route::get('/', 'index');              // GET /locations
            Route::post('/', 'addLocation');       // POST /locations
            Route::get('/{id}', 'show');           // GET /locations/{id}
            Route::put('/{id}', 'update');         // PUT /locations/{id}
            Route::delete('/{id}', 'destroy');     // DELETE /locations/{id}
        });

    // ========== Ratings ==========
    Route::prefix('ratings')
        ->controller(RatingController::class)
        ->group(function () {
            Route::get('/customers', 'getAllCustomerRatings'); // GET /ratings/customers
            Route::get('/agents', 'getAllAgentRatings');        // GET /ratings/agents
        });
});