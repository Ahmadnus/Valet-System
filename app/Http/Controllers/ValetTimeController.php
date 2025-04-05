<?php

namespace App\Http\Controllers;

use App\app\Traits\ApiResponse;
use App\Models\ValetTime;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use App\Models\Request as UserRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\Requests\CalculateService;
use App\Http\Requests\Requests\ValetTimeRequest;
class ValetTimeController extends Controller
{    use ApiResponse;

    public function __construct(private CalculateService $calculateService)
    {

    }
    public function parkingTime(ValetTimeRequest $request, $req)
{
    $validatedData = $request->validated();

    $data = $this->calculateService->parkingTime($validatedData, $req);

    return $this->successResponse('Parking time calculated successfully!', $data);
}

}