<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeeWaiverController;
use App\Http\Controllers\TruckungController;
use App\Http\Middleware\lang;

Route::prefix('tracking')
    ->controller(TruckungController::class)
    ->group(function () {

        // Update location - by valet customer (who shares location)
        Route::post('/update', 'updateLocation')
            ->middleware(['auth:sanctum', 'check.type:Customer', Lang::class]); // POST /tracking/update

        // Get agent's latest location - public (or protected if needed)
        Route::get('/agent/{agent_id}', 'getLocation')
            ->middleware([Lang::class]); // GET /tracking/agent/{agent_id}
    });

