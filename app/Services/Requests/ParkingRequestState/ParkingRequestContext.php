<?php

namespace App\Services\Requests\ParkingRequestState;

use App\Models\Request;
use App\Services\Requests\ParkingRequestState\States\{
    PendingState,
    AcceptState,
    StartState,
    PreparingState,
    DeliveringState,
    CompletedState,
    CanceledState
};
use App\Services\Requests\ParkingRequestState\ParkingRequestStateInterface;

class ParkingRequestContext
{
    protected ParkingRequestStateInterface $state;

    public function __construct(string $status)
    {
        $this->state = match ($status) {
            Request::STATUS_PENDING => new PendingState(),
            Request::STATUS_ACCEPTED => new AcceptState(),
            Request::STATUS_IN_PROGRESS => new StartState(),
            Request::STATUS_PREPARING => new PreparingState(),
            Request::STATUS_DELIVERING => new DeliveringState(),
            Request::STATUS_COMPLETED => new CompletedState(),
            Request::STATUS_CANCELED => new CanceledState(),
            default => throw new \Exception('Invalid request status.')
        };
    }

    public function handle(Request $request, array $data = [])
    {
        // فقط تأكد من أن $data هي مصفوفة، ولا تصل مباشرة لمفتاح غير موجود في $data
        if (!is_array($data)) {
            $data = [];
        }

        // استدعاء ال handle الخاص بالحالة المحددة
        return $this->state->handle($request, $data);
    }
}
