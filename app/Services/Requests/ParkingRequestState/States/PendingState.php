<?php

namespace App\Services\Requests\ParkingRequestState\States;

use App\Models\Request;
use App\Services\Requests\ParkingRequestState\ParkingRequestStateInterface;
use Illuminate\Support\Facades\Log;

class PendingState implements ParkingRequestStateInterface
{
    public function handle(Request $request, array $data = [])
    {
        // مثال: قبول الطلب فقط من الحالة pending
        if (!isset($data['valet_agent_id'])) {
            throw new \Exception("Missing valet agent ID.");
        }

        $request->update([
            'status' => Request::STATUS_ACCEPTED,
            'valet_agent_id' => $data['valet_agent_id'],
            'in_progress_time' => now(),
        ]);

        Log::info("Request {$request->id} moved from pending to accepted.");

        return $request;
    }
}