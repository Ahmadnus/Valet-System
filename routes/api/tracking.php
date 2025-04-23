<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeeWaiverController;
use App\Http\Controllers\TruckungController;
use App\Http\Middleware\lang;

Route::post('/update-location', [TruckungController::class, 'updateLocation']);
//'check.role:valet_customer'
Route::get('/get-location/{agent_id}', [TruckungController::class, 'getLocation']);


