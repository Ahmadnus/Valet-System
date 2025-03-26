<?php

namespace App\Http\Controllers;

use App\Models\ValetTime;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use App\Models\Request as UserRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\Requests\CalculateService;
use App\Http\Requests\Requests\ValetTimeRequest;
class ValetTimeController extends Controller
{

    public function __construct(private CalculateService $calculateService)
    {

    }
    public function parkingTime(ValetTimeRequest $request, $req)
    {
        // Validate the request data
        $validatedData = $request->validated();

        // Call the service method and pass the request ID
        $data = $this->calculateService->parkingTime($validatedData, $req);

        return response()->json([
            'message' => 'Parking time calculated successfully!',
            'data' => $data
        ], 200);
    }
}