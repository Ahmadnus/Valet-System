<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeeWaiverController;
use App\Http\Controllers\AgentController;
use App\Http\Middleware\lang;


Route::middleware(['auth:sanctum', 'check.type:Agent', Lang::class])->prefix('agent')->group(function () {
    Route::get('myHost', [AgentController::class, 'dashboard']);
});



