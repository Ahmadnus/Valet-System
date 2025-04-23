
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeeWaiverController;
use App\Http\Controllers\HostController;
use App\Http\Middleware\lang;


Route::prefix('host')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/fee-waiver/{id}', [FeeWaiverController::class, 'handleFeeWaiver']);
    // Get all agents working under the valet_host's locations
    Route::get('/agents', [HostController::class, 'indexAgents'])->name('agents.index');

    // You can add more routes under host role here if needed...
});