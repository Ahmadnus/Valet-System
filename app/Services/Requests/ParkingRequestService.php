<?php

namespace App\Services\Requests;


use App\Models\Request;
use App\Models\Car;
use App\Models\User;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;
use App\NotificationType;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Laravel\Firebase\Facades\Firebase;

class ParkingRequestService
{

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function createParkingRequest(array $data)
{
    $userId = Auth::id();

    $ownsCar = Car::where('id', $data['car_id'])
        ->where('user_id', $userId)
        ->exists();

    if (!$ownsCar) {
        Log::warning("User $userId attempted to request parking for a car they don't own.");
        throw new Exception('You can only request parking for a car you own.');
    }

    $existingRequest = Request::where('user_id', $userId)
        ->where('car_id', $data['car_id'])
        ->whereIn('status', ['pending', 'in_progress'])
        ->exists();

    if ($existingRequest) {
        Log::info("User $userId attempted to create a duplicate parking request.");
        throw new Exception('You have already sent a request. Please wait until it is completed.');
    }

    $request = Request::create([
        'user_id' => $userId,
        'car_id' => $data['car_id'],
        'location_id' => $data['location_id'],
        'status' => 'pending',
        'parking_slot_id' => $data['parking_slot_id'],
    ]);

    Log::info("Parking request {$request->id} created by user $userId.");

    // Send notification to valet agent using topic
    app(NotificationService::class)->sendNotification(
        title: 'طلب ركن جديد',
        body: 'تم إنشاء طلب ركن سيارة جديد من قبل أحد العملاء.',
        data: [
            'request_id' => $request->id,
            'status' => 'pending',
        ],
        user: null,
        topic: \App\TopicType::VALET_AGENT,
        type: NotificationType::REQUEST_PARKING_SERVICE
    );



    return $request;
}

public function acceptParkingRequest(int $requestId)
{
    $valetAgentId = Auth::id();

    if (!Auth::user()->hasRole('valet_agent')) {
        Log::warning("User $valetAgentId attempted to accept parking request $requestId without permission.");
        throw new Exception('Unauthorized. Only valet agents can accept requests.');
    }

    return DB::transaction(function () use ($requestId, $valetAgentId) {
        $parkingRequest = Request::where('id', $requestId)
            ->where('status', 'pending')->with('parkingSlot')
            ->first();



        if (!$parkingRequest) {
            Log::info("Parking request $requestId not found or already accepted.");
            throw new Exception('Request not found or already accepted.');
        }

        $parkingRequest->update([
            'status' => Request::STATUS_IN_PROGRESS,
            'valet_agent_id' => $valetAgentId,
            'in_progress_time' => now(),
        ]);



            $parkingRequest->parkingSlot()->update([
                'status' => 'occupied',
            ]);


        Log::info("Parking request $requestId accepted by valet agent $valetAgentId.");

        return $parkingRequest;
    });
}


public function requestCarPreparation(int $requestId)
{
    return DB::transaction(function () use ($requestId) {
        $parkingRequest = Request::where('id', $requestId)
            ->where('status', Request::STATUS_IN_PROGRESS)
            ->first();

        if (!$parkingRequest) {
            Log::info("Request $requestId not found or not in progress.");
            throw new Exception('Request not found or not in progress.');
        }

        $timeDifference = $parkingRequest->calculateTimeDifference();

        $parkingRequest->update([
            'status' => Request::STATUS_PREPARING,
            'completed_time' => now(),
            'total_hours' => $timeDifference,
        ]);

        Log::info("Request $requestId moved to preparing status.");

        return $parkingRequest;
    });
}

public function completeParkingRequest(int $requestId, string $plateNumber)
{
    return DB::transaction(function () use ($requestId, $plateNumber) {
        $valetAgentId = Auth::id();

        if (!Auth::user()->hasRole('valet_agent')) {
            Log::warning("User $valetAgentId attempted to complete request $requestId without permission.");
            throw new Exception('Unauthorized. Only valet agents can complete requests.');
        }

        $parkingRequest = Request::where('id', $requestId)
            ->where('status', Request::STATUS_PREPARING)
            ->first();

        if (!$parkingRequest) {
            Log::info("Request $requestId not found or not ready for completion.");
            throw new Exception('Request not found or not ready for completion.');
        }

        $car = Car::where('id', $parkingRequest->car_id)
            ->where('plate_number', $plateNumber)
            ->first();

        if (!$car) {
            Log::warning("Plate number mismatch for car ID {$parkingRequest->car_id} on request $requestId.");
            throw new Exception('Plate number does not match the registered car.');
        }

        $parkingRequest->update([
            'status' => Request::STATUS_COMPLETED,
        ]);

        Log::info("Request $requestId completed by valet agent $valetAgentId.");

        return $parkingRequest;
    });
}
}