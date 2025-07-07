<?php

namespace App\Services\Requests\ParkingRequestState\States;

use App\Models\LocationAgent;
use App\Models\Request;
use App\Services\Requests\ParkingRequestState\ParkingRequestStateInterface;
use App\Traits\app\Traits\ChecksValetAgentRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StartState implements ParkingRequestStateInterface
{
    use ChecksValetAgentRole;
    public function handle(Request $request, array $data = [])
    {
        $user = $this->ensureValetAgent('Start Request');
        $valetAgentId = $user->id;

        if (
            $request->status !== Request::STATUS_ACCEPTED ||
            $request->valet_agent_id !== $valetAgentId
        ) {
            Log::warning("Agent $valetAgentId tried to start request {$request->id} but is unauthorized or status is invalid.");
            throw new \Exception(trans('You are not authorized to start this request.'));
        }

        $request->update([
            'status' => Request::STATUS_IN_PROGRESS,
            'in_progress_time' => now(),
        ]);

        LocationAgent::where('agent_id', $valetAgentId)
            ->where('location_id', $request->location_id)
            ->update(['status' => 'available']);

        Log::info("Request {$request->id} moved to in_progress by valet agent $valetAgentId.");

        return $request;
    }
}
