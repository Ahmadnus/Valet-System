<?php

namespace App\Http\Resources\Request;

use App\Http\Resources\Car\CarResource;
use App\Http\Resources\location\LocationResource;
use App\Http\Resources\ParkingSlotResource;
use App\Http\Resources\UserResource;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,

            'user' => $this->whenLoaded('user', new UserResource($this->user)),
            'car' => $this->whenLoaded('car',  new CarResource($this->car)),
            'location' => $this->whenLoaded('location', new LocationResource($this->location)),
            'status' => $this->status,
            'parking_slot' => $this->whenLoaded('parkingSlot',  new ParkingSlotResource($this->parkingSlot)),
            'in_progress_time' => $this->in_progress_time,
            'completed_time' => $this->completed_time,
        ];
    }
}
