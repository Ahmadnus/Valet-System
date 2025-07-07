<?php

namespace App\Http\Controllers;

use App\app\Traits\ApiResponse as TraitsApiResponse;
use App\Http\Requests\CheckPlateNumRequest;
use App\Http\Requests\TradRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Car;
use App\Models\Location;
use App\Models\Request as userRequest;
use App\Models\LocationAgent;
use App\Models\Payment;
use App\Services\TradRequestService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Traits\ApiResponse;
use Spatie\Permission\Models\Role;

class TradParkingController extends Controller
{


    use TraitsApiResponse;

    protected $service;

    public function __construct(TradRequestService $service)
    {
        $this->service = $service;
    }

    public function parkGuestCar(TradRequest $request)
    {
        $parkingRequest = $this->service->park($request->validated());

        return $this->successResponse(trans('Guest parking request created and started successfully.'), [
            'request_id' => $parkingRequest->id,
        ]);
    }

    public function createPayment(CheckPlateNumRequest $request, int $id)
    {
        [$payment, $amount, $hours] = $this->service->createPayment($request->validated(), $id);

        return $this->successResponse(trans('Payment created successfully.'), [
            'payment_id' => $payment->id,
            'amount' => $amount,
            'total_hours' => $hours,
        ]);
    }

    public function completeGuestValet(CheckPlateNumRequest $request, int $id)
    {
        $parkingRequest = $this->service->complete($id, $request->validated());

        return $this->successResponse(trans('Guest parking request marked as completed.'), [
            'request_id' => $parkingRequest->id,
        ]);
    }

    public function getTodayCashEarningsPerLocation()
    {
        $data = $this->service->getTodayCashEarningsPerLocation();

        return $this->successResponse(trans('Cash earnings per location fetched successfully.'), [
            'total_cash_by_location' => $data,
        ]);
    }

    public function getTodayTotalCash()
    {
        $data = $this->service->getTodayTotalCash();

        return $this->successResponse(trans('Total cash earnings fetched successfully.'), $data);
    }
}
