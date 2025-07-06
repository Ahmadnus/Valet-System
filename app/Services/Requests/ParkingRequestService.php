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
use App\Events\CarPreparationRequested;
use App\Events\DeliveryRequestAccepted;
use App\Events\ParkingRequestAccepted;
use App\Events\ParkingRequestCanceled;
use App\Events\ParkingRequestCreated;
use App\Events\TruckingCar;
use App\Models\Location;
use App\Services\Requests\ParkingRequestState\ParkingRequestContext;
use App\Services\Requests\ParkingRequestState\States\StartState;
use App\Traits\app\Traits\CheckDistanceTrait;
use Spatie\Permission\Models\Role;

class ParkingRequestService
{
    use CheckDistanceTrait;
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

            $userLat = $data['latitude'];
            $userLng = $data['longitude'];


            if (!$this->isUserWithinDistance($userLat, $userLng)) {
                throw new \Exception(trans('You are too far from the nearest location. Please choose a closer location.'));
            }


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

            event(new ParkingRequestCreated($request));

            return $request;
        });
    }

    public function changeParkingRequestStatus(int $requestId, string $status, array $data = [])
    {

        return DB::transaction(function () use ($requestId, $status, $data) {

            $request = $this->getParkingRequest($requestId);
            $context = new ParkingRequestContext($status);
            return $context->handle($request, $data);
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

    private function getParkingRequest(int $requestId)
    {
        $parkingRequest = Request::find($requestId);

        if (!$parkingRequest) {
            Log::info("Parking request $requestId not found.");
            throw new Exception(trans("Request not found."));
        }

        return $parkingRequest;
    }
}