


<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoyaltyPointController;
use App\Http\Controllers\FeeWaiverController;
use App\Http\Middleware\lang;



Route::middleware(['auth:sanctum', 'check.type:Customer', Lang::class])->group(function () {

    // Loyalty Points Routes
    Route::prefix('loyalty')
        ->controller(LoyaltyPointController::class)
        ->group(function () {
            Route::get('/points', 'show');                                 // GET    /loyalty/points
            Route::get('/check', 'checkLoyaltyPoints');                    // GET    /loyalty/check
            Route::post('/earn', 'earnLoyaltyPoints');                     // POST   /loyalty/earn
            Route::post('/use/{request_id}', 'useLoyaltyPoints');          // POST   /loyalty/use/{request_id}
            Route::post('/generate-invoice', 'generateInvoice');           // POST   /loyalty/generate-invoice
        });

    // Fee Waiver Routes
    Route::prefix('fee-waiver')
        ->controller(FeeWaiverController::class)
        ->group(function () {
            Route::post('/request/{requestId}', 'requestFeeWaiver');       // POST /fee-waiver/request/{requestId}
        });
});