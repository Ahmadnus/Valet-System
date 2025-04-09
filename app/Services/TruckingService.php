<?php

namespace App\Services;
use App\Models\Car;
use App\Models\Request as ModelRequest;
use App\Models\AgentLocation;
use App\Events\TruckingCar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;
class TruckingService
{
    /**
     * Create a new class instance.
     */

    public function updateLocation($data)
    {
        try {

            DB::beginTransaction();

            $modelRequest = ModelRequest::where('valet_agent_id', $data['agent_id'])
                ->where('status', 'accepted')
                ->first();

            if (!$modelRequest) {
                throw new Exception('No active request found for this agent');
            }

            $car = Car::find($modelRequest->car_id);
            if (!$car) {
                throw new Exception('Car not found');
            }

            if (Auth::id() !== $car->user_id) {
                throw new Exception('Unauthorized access');
            }
            $loc=['agent_id' => $data['agent_id'], 'latitude' => $data['latitude'], 'longitude' => $data['longitude']];

            $location = AgentLocation::updateOrCreate(

                $loc
            );

            DB::commit();


            broadcast(new TruckingCar($data['agent_id'], $data['latitude'], $data['longitude']));

            Log::info("Location updated for agent ID {$data['agent_id']} on car ID {$car->id}");
            return $location;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error updating location: " . $e->getMessage());
            throw $e;
        }
    }
    public function getLocation($agentId)
    {
        $location = AgentLocation::where('agent_id', $agentId)->first();

        if (!$location) {
            throw new Exception('Location not found');
        }

        return $location;
    }


}
