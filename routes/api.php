<?php


use App\Http\Controllers\AgentController;
use App\Http\Controllers\TradParkingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CarController;
use App\Http\Controllers\CartypeController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FeeWaiverController;
use App\Http\Controllers\HostController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ValetTimeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\NotificationController;
use App\Http\Middleware\CheakUser;
use App\Http\model\Payment;
use App\Models\Payment as ModelsPayment;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\TruckungController;
use App\Http\Controllers\LoyaltyPointController;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Middleware\lang;

require __DIR__.'/api/auth.php';
require __DIR__.'/api/request.php';
require __DIR__.'/api/car.php';
require __DIR__.'/api/rating.php';
require __DIR__.'/api/valet_provider.php';
require __DIR__.'/api/loyalty.php';
require __DIR__.'/api/payment.php';
require __DIR__.'/api/valet_host.php';
require __DIR__.'/api/valet_agent.php';
require __DIR__.'/api/chat.php';
require __DIR__.'/api/tracking.php';



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

if (config('app.debug')) {
    // Local-dev convenience only: hands back the cached OTP so the mobile
    // app can auto-fill it without needing a real SMS/Telegram gateway.
    // Never reachable when APP_DEBUG=false.
    Route::get('/auth/dev-otp', function (Request $request) {
        $phone = $request->query('phone_number');
        $otp = \Illuminate\Support\Facades\Cache::get("otp_{$phone}");

        return response()->json([
            'success' => (bool) $otp,
            'message' => $otp ? 'OTP found.' : 'No pending OTP for this number.',
            'data' => ['otp' => $otp],
            'code' => 200,
        ]);
    });
}




Broadcast::routes(['middleware' => ['auth:sanctum']]);



