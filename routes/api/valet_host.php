
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeeWaiverController;
use App\Http\Controllers\HostController;
use App\Http\Middleware\lang;


Route::prefix('host')
    ->middleware(['auth:sanctum', Lang::class])
    ->group(function () {
        Route::post('/fee-waiver/{fee_waiver}', [FeeWaiverController::class, 'handleFeeWaiver']);
        Route::get('/agents', [HostController::class, 'indexAgents'])->name('agents.index');


    });