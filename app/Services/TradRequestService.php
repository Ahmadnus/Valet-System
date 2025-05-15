<?php

namespace App\Services;
use App\Models\User;
use App\Models\Car;
use App\Models\Location;
use App\Models\LocationAgent;
use App\Models\Payment;
use App\Models\ValetTime;
use App\Models\Request as userRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\app\Traits\HasRequestValidation;
class TradRequestService
{
    use HasRequestValidation;
    /**
     * Create a new class instance.
     */

     public function park(array $data)
     {
         return DB::transaction(function () use ($data) {
             $agentId = Auth::id();
             $isAuthorized = LocationAgent::where('agent_id', $agentId)
             ->where('location_id', $data['location_id'])
             ->exists();

         if (!$isAuthorized) {
             throw new \Exception(trans('You are not authorized to create a parking request for this location.'));
         }

             $user = User::firstOrCreate(
                 ['phone_number' => $data['phone_number']],
                 [
                     'name' => null,
                     'email' => null,
                     'password' => bcrypt(Str::random(10)),
                 ]
             );

             if (!$user->hasRole('valet_guest')) {
                 $role = Role::firstOrCreate(['name' => 'valet_guest']);
                 $user->assignRole($role);
             }

             $car = Car::firstOrCreate(
                 ['plate_number' => $data['plate_number']],
                 [
                     'user_id' => $user->id,
                     'cartypeable_id' => $data['cartypeable_id'],
                     'cartypeable_type' => \App\Models\CarType::class,
                 ]
             );


             $inProgressRequest = userRequest::where('car_id', $car->id)
                 ->where('status', userRequest::STATUS_IN_PROGRESS)
                 ->first();

             if ($inProgressRequest) {
                 throw new \Exception(trans('This car is already in progress.'));
             }


             $awaitingPayment = userRequest::where('car_id', $car->id)
                 ->whereIn('status', ['preparing', 'delivering'])
                 ->first();

             if ($awaitingPayment) {
                 throw new \Exception(trans('This request is waiting for payment. Please complete the payment first.'));
             }


             $existingRequest = userRequest::where('user_id', $user->id)
                 ->where('car_id', $car->id)
                 ->whereIn('status', ['pending', 'accepted'])
                 ->first();

             if ($existingRequest) {
                 $existingRequest->update([
                     'valet_agent_id' => $agentId,
                     'status' => userRequest::STATUS_IN_PROGRESS,
                     'in_progress_time' => now(),
                     'requested_by_user' => true,
                 ]);

                 $parkingRequest = $existingRequest;
             } else {
                 $parkingRequest = userRequest::create([
                     'user_id' => $user->id,
                     'car_id' => $car->id,
                     'valet_agent_id' => $agentId,
                     'location_id' => $data['location_id'],
                     'parking_slot_id' => $data['parking_slot_id'],
                     'status' => userRequest::STATUS_IN_PROGRESS,
                     'in_progress_time' => now(),
                     'requested_by_user' => $data['requested_by_user'] ?? false,
                 ]);
             }

             LocationAgent::where('agent_id', $agentId)
                 ->where('location_id', $data['location_id'])
                 ->update(['status' => 'occupied']);

             return $parkingRequest;
         });
     }
     public function createPayment(array $data, int $id)
     {
         return DB::transaction(function () use ($data, $id) {
             $request = $this->getGuestRequest($id);


             if (in_array($request->status, [
                 userRequest::STATUS_PENDING,
                 userRequest::STATUS_ACCEPTED,
             ])) {
                 throw new \Exception(trans('Cannot create a payment before the parking is in progress.'));
             }
             if ($request->payment) {
                throw new \Exception(trans('A payment already exists for this request.'));
            }

             $this->validatePlateNumber($request, $data['plate_number']);


             $totalHours = abs($request->in_progress_time
                 ? now()->diffInHours($request->in_progress_time)
                 : 0);

             $request->update(
                ['total_hours' => $totalHours,
            'requested_by_user' => 1]);

             $location = Location::findOrFail($request->location_id);

             $amount = ValetTime::where('request_id',$id)->first();
             dd( $amount);

             $payment = Payment::create([
                 'request_id'     => $request->id,
                 'amount'         => $amount->price,
                 'status'         => 'pending',
                 'payment_method' => $data['payment_method'] ?? 'cash',
             ]);

             return [$payment, $amount, $totalHours];
         });
     }
     public function complete(int $id, array $data)
     {
         return DB::transaction(function () use ($id, $data) {
             $request = $this->getGuestRequest($id);

             $this->validatePlateNumber($request, $data['plate_number']);

             $payment = Payment::where('request_id', $request->id)
                 ->where('status', 'pending')
                 ->first();

             if (!$payment) {
                 $payment = Payment::create([
                     'request_id'     => $request->id,
                     'status'         => 'completed',
                     'payment_method' => 'guest_assumed',
                     'amount'         => 0,
                 ]);
             } else {
                 $payment->update(['status' => 'completed']);
             }

             $request->update([
                 'status'          => userRequest::STATUS_COMPLETED,
                 'completed_time'  => now(),
             ]);

             LocationAgent::where('agent_id', Auth::id())
                 ->where('location_id', $request->location_id)
                 ->update(['status' => 'available']);

             return $request;
         });
     }
     private function getGuestRequest(int $id)
{
    $request = userRequest::with('user.roles')->findOrFail($id);

    if (!$request->user->getRoleNames()->contains('valet_guest')) {
        throw new \Exception(trans('This request does not belong to a guest user.'));
    }

    return $request;
}

private function validatePlateNumber($request, string $plateNumber)
{
    $car = Car::where('plate_number', $plateNumber)->first();

    if (!$car || $request->car_id !== $car->id) {
        throw new \Exception(trans('The plate number does not match the car in this request.'));
    }
}
public function getTodayCashEarningsPerLocation()
{
    $cashPayments = Payment::where('payment_method', 'cash')
        ->where('status', 'completed')
        ->whereDate('created_at', now()->toDateString())
        ->with('request')
        ->get();

    $grouped = $cashPayments->groupBy(function ($payment) {
        return optional($payment->request)->location_id;
    })->map(function ($group) {
        return $group->sum('amount');
    });

    return $grouped;
}

public function getTodayTotalCash()
{
    $cashPayments = Payment::where('payment_method', 'cash')
        ->where('status', 'completed')
        ->whereDate('created_at', now()->toDateString())
        ->with('request')
        ->get();

    $totalCash = $cashPayments->sum('amount');

    return [
        'total_cash' => $totalCash,
        'payments' => $cashPayments
    ];
}

}
