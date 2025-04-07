<?php

namespace App\Http\Resources\Car;

use App\Http\Resources\CarTypeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'key_box' => $this->key_box,
            'cartypeable_id' => $this->cartypeable_id,
            'cartypeable_type' => $this->cartypeable_type,
            'plate_number' => $this->plate_number,
            'car_type' => new CarTypeResource($this->whenLoaded('cartypeable')),

        ];
    }
}
