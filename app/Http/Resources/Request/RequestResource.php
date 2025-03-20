<?php

namespace App\Http\Resources\Request;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'car_id' => $this->car_id,
            'location_id' => $this->location_id,
            'status' => $this->status,
            'request_time' => $this->request_time,
            'valet_agent_id' => $this->valet_agent_id ?? null,
            'in_progress_time' => $this->in_progress_time ?? null,
            'completed_time' => $this->completed_time ?? null,
        ];
    }
}
