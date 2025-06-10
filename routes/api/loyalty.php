


<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoyaltyPointController;
use App\Http\Controllers\FeeWaiverController;
use App\Http\Middleware\lang;



Route::middleware(['auth:sanctum', 'check.role:valet_customer', Lang::class])->group(function () {

    Route::get('/loyalty-points', [LoyaltyPointController::class, 'show']);
    Route::post('/use-loyalty-points/{request_id}', [LoyaltyPointController::class, 'useLoyaltyPoints']);
    Route::post('/earn-loyalty-points', [LoyaltyPointController::class, 'earnLoyaltyPoints']);
    Route::post('/generate-invoice', [LoyaltyPointController::class, 'generateInvoice']);
    Route::get('/cheak-points', [LoyaltyPointController::class, 'checkLoyaltyPoints']);

    Route::post('/fee-waiver/request/{requestId}', [FeeWaiverController::class, 'requestFeeWaiver']);


});
