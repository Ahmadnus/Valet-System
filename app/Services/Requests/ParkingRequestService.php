<?php

namespace App\Services\Requests;


use App\Models\Request;
use App\Models\Car;
use App\Models\User;
use App\Exceptions\ParkingRequestException;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Gate;
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

        // Check if the user owns the car
        $this->checkCarOwnership($userId, $data['car_id']);

        // Check if there is already an active parking request
        $this->checkExistingRequest($userId, $data['car_id']);

        // Create the parking request
        $request = Request::create([
            'user_id' => $userId,
            'car_id' => $data['car_id'],
            'location_id' => $data['location_id'],
            'status' => 'pending',
            'parking_slot_id' => $data['parking_slot_id'],
        ]);

        Log::info("Parking request {$request->id} created by user $userId.");

        // Send notification to valet agent using topic
        $this->notificationService->sendNotification(
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

    // Ensure valet agent has permission to accept


 {
        $parkingRequest = $this->getParkingRequest($requestId, 'pending');

        $parkingRequest->update([
            'status' => Request::STATUS_ACCEPTED,
            'valet_agent_id' => $valetAgentId,
            'in_progress_time' => now(),
        ]);

        Log::info("Parking request $requestId accepted by valet agent $valetAgentId.");

        return $parkingRequest;

}}
public function startParkingRequest(int $requestId)
{
    $valetAgentId = Auth::id();


        $parkingRequest = Request::where('id', $requestId)
            ->where('status', Request::STATUS_ACCEPTED)
            ->where('valet_agent_id', $valetAgentId)
            ->with('parkingSlot')
            ->first();

        if (!$parkingRequest) {
            Log::info("Request $requestId not found or not in accepted state.");
            throw new Exception('Request not found or not in accepted state.');
        }

        // Update to in_progress
        $parkingRequest->update([
            'status' => Request::STATUS_IN_PROGRESS,
            'in_progress_time' => now(),
        ]);

        // Now occupy the parking slot


        Log::info("Request $requestId moved to in_progress by valet agent $valetAgentId and slot marked as occupied.");

        return $parkingRequest;

}
public function requestCarPreparation(int $requestId)
{
    $parkingRequest = $this->getParkingRequest($requestId, Request::STATUS_IN_PROGRESS);

    $parkingRequest->update([
        'status' => Request::STATUS_PREPARING,
        'completed_time' => now(),
        // don't calculate timeDifference here
    ]);

    Log::info("Request $requestId moved to preparing status.");

    return $parkingRequest;
}


    // Helper Methods

    private function checkCarOwnership(int $userId, int $carId)
    {
        $ownsCar = Car::where('id', $carId)
            ->where('user_id', $userId)
            ->exists();

        if (!$ownsCar) {
            Log::warning("User $userId attempted to request parking for a car they don't own.");
            throw new Exception('You can only request parking for a car you own.');
        }
    }

    private function checkExistingRequest(int $userId, int $carId)
    {
        $existingRequest = Request::where('user_id', $userId)
            ->where('car_id', $carId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->exists();

        if ($existingRequest) {
            Log::info("User $userId attempted to create a duplicate parking request.");
            throw new Exception('You have already sent a request. Please wait until it is completed.');
        }
    }

    private function authorizeValetAgent(int $valetAgentId)
    {
        if (!Auth::user()->hasRole('valet_agent')) {
            Log::warning("User $valetAgentId attempted to accept parking request without permission.");
            throw new Exception('Unauthorized. Only valet agents can accept requests.');
        }
    }

    private function getParkingRequest(int $requestId, string $status)
    {
        $parkingRequest = Request::where('id', $requestId)
            ->where('status', $status)
            ->first();

        if (!$parkingRequest) {
            Log::info("Parking request $requestId not found or already processed.");
            throw new Exception("Request not found or already processed.");
        }

        return $parkingRequest;
    }


}