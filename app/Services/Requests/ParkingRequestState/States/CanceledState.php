<?php

namespace App\Services\Requests\ParkingRequestState\States;


use App\Events\ParkingRequestCanceled;
use App\Models\RejectedRequest;
use App\Models\Request;
use App\Services\Requests\ParkingRequestState\ParkingRequestStateInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
class CanceledState implements ParkingRequestStateInterface
{
    public function handle(Request $request, array $data = [])
    {
        {
            $user = Auth::user();

            if (!$user->hasRole('valet_agent')) {
                Log::warning("User {$user->id} attempted to cancel request {$request->id} without valet_agent role.");
                throw new \Exception(trans('Unauthorized: You must be a valet agent to cancel this request.'));
            }

            $userId = $user->id;
            $reason = $data['reason'] ?? null;

            if ($request->status !== Request::STATUS_PENDING) {
                Log::warning("User $userId tried to cancel request {$request->id} which is not pending.");
                throw new \Exception(trans("Only pending requests can be canceled."));
            }

            $request->update([
                'status' => Request::STATUS_CANCELED,
            ]);

            RejectedRequest::create([
                'user_id' => $userId,
                'plate_number' => $request->car->plate_number,
                'cartypeable_type' => get_class($request->car),
                'cartypeable_id' => $request->car->id,
                'reason' => $reason,
            ]);

            event(new ParkingRequestCanceled($request, $reason));

            Log::info("Parking request {$request->id} has been canceled by user $userId. Reason: $reason.");

            return $request;
        }}
}