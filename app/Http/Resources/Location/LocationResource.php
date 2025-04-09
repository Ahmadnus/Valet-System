<?php

namespace App\Http\Resources\Location;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'distance' => $this->distance, // This comes from your raw select query
            'name' => $this->name, // Use translation based on the language
            'parking_slots' => $this->whenLoaded('parkingSlots'), // Assuming you have a ParkingSlotResource
        ];
    }
}
