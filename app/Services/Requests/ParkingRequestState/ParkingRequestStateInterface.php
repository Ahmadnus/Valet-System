<?php

namespace App\Services\Requests\ParkingRequestState;
use App\Models\Request;

interface ParkingRequestStateInterface
{
    public function handle(Request $request, array $data = []);
}
