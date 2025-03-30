<?php

namespace App\Http\Resources\Request;

use App\Http\Resources\Car\CarResource;
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


            'id' => $this->id,
            'user' => $this->user ? $this->user->toArray() : null,
            'car' => $this->car ? $this->car->toArray() : null,
            'location' => $this->location ? $this->location->toArray() : null,
            'status' => $this->status,
            'valet_agent' => $this->valetAgent ? $this->valetAgent->toArray() : null,
            'parking_slot' => $this->parkingSlot ? $this->parkingSlot->toArray() : null,
            'in_progress_time' => $this->in_progress_time ?? null,
            'completed_time' => $this->completed_time ?? null,
        ];
    }
}
