


<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoyaltyPointController;
use App\Http\Controllers\FeeWaiverController;
use App\Http\Middleware\lang;

Route::get('/loyalty-points', [LoyaltyPointController::class, 'show']);
Route::post('/use-loyalty-points/{request_id}', [LoyaltyPointController::class, 'useLoyaltyPoints']);
Route::post('/earn-loyalty-points', [LoyaltyPointController::class, 'earnLoyaltyPoints']);
Route::post('/generate-invoice', [LoyaltyPointController::class, 'generateInvoice']);
Route::middleware('auth:sanctum')->get('/cheak-points', [LoyaltyPointController::class, 'checkLoyaltyPoints']);

Route::post('/fee-waiver/request/{id}', [FeeWaiverController::class, 'requestFeeWaiver']);
