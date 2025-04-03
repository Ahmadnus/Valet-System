<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentLocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {   return [
        'success' => true,
        'agent_id' => $this->agent_id,
        'latitude' => $this->latitude,
        'longitude' => $this->longitude,
        'updated_at' => $this->updated_at->toDateTimeString(),
    ];
    }
}
