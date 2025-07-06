
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeeWaiverController;
use App\Http\Controllers\HostController;
use App\Http\Middleware\lang;



Route::prefix('host')
    ->middleware(['auth:sanctum', 'check.type:Host', Lang::class])
    ->group(function () {

        // ========== Fee Waiver Handling ==========
        Route::controller(FeeWaiverController::class)->group(function () {
            Route::post('/fee-waiver/{fee_waiver}', 'handleFeeWaiver'); // POST /host/fee-waiver/{fee_waiver}
        });

        // ========== Agent Management ==========
        Route::controller(HostController::class)->group(function () {
            Route::get('/agents', 'indexAgents')->name('agents.index'); // GET /host/agents
        });

    });