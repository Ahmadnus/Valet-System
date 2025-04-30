<?php

namespace App\Services\Requests;


use App\Models\LocationAgent;
use App\Models\Request;
use App\Models\Car;
use App\Models\User;
use App\Exceptions\ParkingRequestException;
use App\Models\Notification;
use App\Models\ParkingSlot;
use App\Models\RejectedRequest;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;
use App\NotificationType;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Laravel\Firebase\Facades\Firebase;
use App\app\Traits\HasRequestValidation;
use App\Models\Location;
use Spatie\Permission\Models\Role;

class ParkingRequestService
{
    use HasRequestValidation;
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }


    public function createParkingRequest(array $data)
    {
        return DB::transaction(function () use ($data) {

            $userId = Auth::id();


            $this->checkCarOwnership($userId, $data['car_id']);
            $this->checkExistingRequest($userId, $data['car_id']);


            $request = Request::create([
                'user_id' => $userId,
                'car_id' => $data['car_id'],
                'location_id' => $data['location_id'],
                'status' => 'pending',
                'parking_slot_id' => $data['parking_slot_id'],
                'customer_latitude' => $data['latitude'],
                'customer_longitude' => $data['longitude'],
            ]);

            Log::info("Parking request {$request->id} created by user $userId.");


            $location = Location::find($data['location_id']);

            if ($location) {

                $agents = $location->agents()
                    ->where('status', 'available')
                    ->whereHas('fcmTokens')
                    ->get();


                $notifiedAgents = $agents->filter(function ($agent) {
                    return $agent->getRoleNames()->contains('valet_agent');
                });

                $tokens = $notifiedAgents->flatMap(function ($agent) {
                    return $agent->fcmTokens->pluck('token');
                })->toArray();


                if ($notifiedAgents->isNotEmpty()) {
                    Log::info('Valet agents notified for request ID ' . $request->id, [
                        'agents' => $notifiedAgents->pluck('id', 'name')->toArray(),
                        'tokens' => $tokens,
                    ]);
                }


                if (!empty($tokens)) {
                    $this->notificationService->sendMulticastNotification(
                        title: trans('New Parking Request'),
                        body: trans('A new parking request has been created by a customer.'),
                        tokens: $tokens,
                        data: [
                            'request_id' => $request->id,
                            'status' => 'pending',
                        ],
                        type: NotificationType::REQUEST_PARKING_SERVICE
                    );
                }
            }

            return $request;
        });
    }

    public function acceptParkingRequest(int $requestId)
{
    return DB::transaction(function () use ($requestId) {
        $valetAgentId = Auth::id();

        $parkingRequest = $this->getParkingRequest($requestId, Request::STATUS_PENDING);

        $agentStatus = LocationAgent::where('agent_id', $valetAgentId)
            ->where('location_id', $parkingRequest->location_id)
            ->value('status');

        if ($agentStatus !== 'available') {
            Log::warning("Agent $valetAgentId tried to accept request $requestId but is not available.");
            throw new \Exception(trans('You cannot accept the request because you are not available at the moment.'));
        }


        $parkingRequest->update([
            'status' => Request::STATUS_ACCEPTED,
            'valet_agent_id' => $valetAgentId,
            'in_progress_time' => now(),
        ]);


        LocationAgent::where('agent_id', $valetAgentId)
            ->where('location_id', $parkingRequest->location_id)
            ->update(['status' => 'occupied']);

        Log::info("Parking request $requestId accepted by valet agent $valetAgentId.");


        $customer = $parkingRequest->user;

        $tokens = $customer->fcmTokens->pluck('token')->toArray();

        if (!empty($tokens)) {
            $this->notificationService->sendMulticastNotification(
                title: trans('Request Accepted'),
                body: trans('Your parking request has been accepted by a valet agent.'),
                tokens: $tokens,
                data: [
                    'request_id' => $parkingRequest->id,
                    'status' => Request::STATUS_ACCEPTED,
                ],
                type: NotificationType::ACCEPT_REQUESTED
            );

            Log::info("Sent acceptance notification for request ID {$requestId} to user ID {$customer->id}.", [
                'tokens' => $tokens,
            ]);
        }

        return $parkingRequest;
    });
}
    public function startParkingRequest(int $requestId)
    {
        return DB::transaction(function () use ($requestId) {
            $valetAgentId = Auth::id();

            $parkingRequest = Request::where('id', $requestId)
                ->where('status', Request::STATUS_ACCEPTED)
                ->where('valet_agent_id', $valetAgentId)
                ->with('parkingSlot')
                ->first();



            $parkingRequest->update([
                'status' => Request::STATUS_IN_PROGRESS,
                'in_progress_time' => now(),
            ]);

            LocationAgent::where('agent_id', $valetAgentId)
                ->where('location_id', $parkingRequest->location_id)
                ->update(['status' => 'available']);

            Log::info("Request $requestId moved to in_progress by valet agent $valetAgentId.");

            return $parkingRequest;
        });
    }
    public function requestCarPreparation(int $requestId, float $latitude, float $longitude)
{
    return DB::transaction(function () use ($requestId, $latitude, $longitude) {
        $userId = Auth::id();

        $parkingRequest = $this->getParkingRequest($requestId, Request::STATUS_IN_PROGRESS);

        if ($parkingRequest->user_id !== $userId) {
            Log::warning("User  tried to prepare a request they don't own.");
            throw new \Exception(trans("You are not allowed to prepare this request."));
        }

        $parkingRequest->update([
            'status' => Request::STATUS_PREPARING,
            'completed_time' => now(),
            'customer_latitude' => $latitude,
            'customer_longitude' => $longitude,
        ]);

        $location = Location::find($parkingRequest->location_id);
        $agents = $location->agents()
            ->where('status', 'available')
            ->whereHas('fcmTokens')
            ->get();

        $tokens = $agents->filter(fn($agent) => $agent->getRoleNames()->contains('valet_agent'))
            ->flatMap(fn($agent) => $agent->fcmTokens->pluck('token'))
            ->toArray();

        if (!empty($tokens)) {
            $this->notificationService->sendMulticastNotification(
                title: 'Car Preparation Requested',
                body: 'A customer has requested their car to be prepared.',
                tokens: $tokens,
                data: [
                    'request_id' => $parkingRequest->id,
                    'status' => Request::STATUS_PREPARING,
                    'customer_latitude' => $latitude,
                    'customer_longitude' => $longitude,
                ],
                type: NotificationType::CAR_PREPARATION_REQUESTED
            );

            Log::info("Car preparation notification sent to available agents in location ID {$location->id}.", [
                'tokens' => $tokens,
            ]);
        }
        Log::info("User $userId requested car preparation for request $requestId.");

        return $parkingRequest;
    });
}

    public function requestCompleted(int $requestId)
{
    return DB::transaction(function () use ($requestId) {
        $userId = Auth::id();

        $parkingRequest = $this->getParkingRequest($requestId, Request::STATUS_IN_PROGRESS);

        if ($parkingRequest->user_id !== $userId) {
            Log::warning("User $userId tried to prepare a request they don't own.");
            throw new \Exception(trans("You are not allowed to prepare this request."));
        }

        if (in_array($parkingRequest->status, [Request::STATUS_PREPARING, Request::STATUS_COMPLETED])) {
            Log::info("Request $requestId is already being prepared or completed.");
            throw new \Exception(trans("This request is already being prepared or has been completed."));
        }

        $parkingRequest->update([
            'status' => Request::STATUS_COMPLETED,
            'completed_time' => now(),
        ]);

        Log::info("User $userId completed the request $requestId.");

        return $parkingRequest;
    });
}

public function acceptDeliveryRequest(int $requestId)
{
    return DB::transaction(function () use ($requestId) {
        $agentId = Auth::id();

        $request = $this->getParkingRequest($requestId, Request::STATUS_PREPARING);



        $request->update([
            'status' => Request::STATUS_DELIVERING,
        ]);
        LocationAgent::where('agent_id', $request->valet_agent_id)
        ->where('location_id', $request->location_id)
        ->update(['status' => 'in_way']);

        Log::info("Agent $agentId accepted delivery for request $requestId.");

        LocationAgent::where('agent_id', $request->valet_agent_id)
            ->where('location_id', $request->location_id)
            ->update(['status' => 'in_way']);

          $customer = $request->user;

          $tokens = $customer->fcmTokens->pluck('token')->toArray();

          if (!empty($tokens)) {
              $this->notificationService->sendMulticastNotification(
                  title: trans('Request Accepted'),
                  body: trans('Your parking request has been accepted by a valet agent.'),
                  tokens: $tokens,
                  data: [
                      'request_id' => $request->id,
                      'status' => Request::STATUS_ACCEPTED,
                  ],
                  type: NotificationType::ACCEPT_PREPARING
              );

              Log::info("Sent acceptance notification for request ID {$requestId} to user ID {$customer->id}.", [
                  'tokens' => $tokens,
              ]);
          }
        return $request;
    });
}
public function completeValet(int $id, $plateNumber)
{
    $this->authorizeValetAgent(Auth::id());

    $parkingRequest = Request::where('id', $id)
        ->whereHas('user.roles', function ($query) {
            $query->where('name', 'valet_guest');
        })
        ->firstOrFail();

    if ($parkingRequest->status !== Request::STATUS_DELIVERING) {
        throw new Exception('Request is not in delivering status.');
    }

    return DB::transaction(function () use ($parkingRequest, $plateNumber) {
        $car = Car::where('plate_number', $plateNumber)->first();

        if (!$car) {
            Log::warning("Plate number $plateNumber not found.");
            throw new \Exception(trans('The plate number does not exist.'));
        }

        if ($parkingRequest->car_id !== $car->id) {
            Log::warning("Mismatch between car in request ({$parkingRequest->car_id}) and provided plate.");
            throw new \Exception('The plate number does not match the car in this request.');
        }

        $parkingRequest->update([
            'status' => Request::STATUS_COMPLETED,
        ]);

        LocationAgent::where('agent_id', Auth::id())
            ->where('location_id', $parkingRequest->location_id)
            ->update(['status' => 'available']);

        Log::info("Request {$parkingRequest->id} completed successfully by agent " . Auth::id());

        return $parkingRequest;
    });
}
    public function cancelParkingRequest(int $requestId, string $reason)
    {
        return DB::transaction(function () use ($requestId, $reason) {
            $userId = Auth::id();


            $parkingRequest = $this->getParkingRequest($requestId, Request::STATUS_PENDING);


            $parkingRequest->update([
                'status' => Request::STATUS_CANCELED,
            ]);


            RejectedRequest::create([
                'user_id' => $userId,
                'plate_number' => $parkingRequest->car->plate_number,
                'cartypeable_type' => get_class($parkingRequest->car),
                'cartypeable_id' => $parkingRequest->car->id,
                'rejection_reason' => $reason,
            ]);

            $customer = $parkingRequest->user;

            $tokens = $customer->fcmTokens->pluck('token')->toArray();

            if (!empty($tokens)) {
                $this->notificationService->sendMulticastNotification(
                    title: trans('Request Canceled'),
                    body: trans('Your parking request has been canceled by the valet agent.'),
                    tokens: $tokens,
                    data: [
                        'request_id' => $parkingRequest->id,
                        'status' => Request::STATUS_CANCELED,
                        'reason' => $reason,
                    ],
                    type: NotificationType::CANCEL_REQUEST
                );

                Log::info("Sent cancellation notification for request ID {$requestId} to user ID {$customer->id}.", [
                    'tokens' => $tokens,
                    'reason' => $reason,
                ]);
            }

            Log::info("Parking request $requestId has been canceled by user $userId. Reason: $reason.");

            return $parkingRequest;
        });
    }


////




    // Utility methods

    private function checkCarOwnership(int $userId, int $carId)
    {
        $ownsCar = Car::where('id', $carId)
            ->where('user_id', $userId)
            ->exists();

        if (!$ownsCar) {
            Log::warning("User $userId attempted to request parking for a car they don't own.");
            throw new Exception(trans('You can only request parking for a car you own.'));
        }
    }

    private function checkExistingRequest(int $userId, int $carId)
{
    $existingRequest = Request::where('user_id', $userId)
        ->where('car_id', $carId)
        ->whereIn('status', ['pending', 'in_progress', 'accepted', 'preparing'])
        ->exists();

    if ($existingRequest) {
        Log::info("User $userId attempted to create a duplicate parking request for car $carId.");
        throw new Exception(trans('You cannot submit a new request for the same car before completing the previous one.'));
    }
}


    private function authorizeValetAgent(int $valetAgentId)
    {
        if (!Auth::user()->hasRole('valet_agent')) {
            Log::warning("User $valetAgentId attempted to accept parking request without permission.");
            throw new Exception(trans('Unauthorized. Only valet agents can accept requests.'));
        }
    }

    private function getParkingRequest(int $requestId, string $status)
    {
        $parkingRequest = Request::where('id', $requestId)
            ->where('status', $status)
            ->first();

        if (!$parkingRequest) {
            Log::info("Parking request $requestId not found or already processed.");
            throw new Exception(trans("Request not found or already processed."));
        }

        return $parkingRequest;
    }
}