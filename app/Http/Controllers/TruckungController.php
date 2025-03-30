<?php

namespace App\Http\Controllers;

use App\Events\TruckingCar;
use Illuminate\Http\Request;

class TruckungController extends Controller
{
    public function sendLocation(Request $request)
{
    $location = $request->input('location');
        broadcast(new TruckingCar($location))->toOthers();

        return response()->json(['success' => true, 'location' => $location]);
}
}