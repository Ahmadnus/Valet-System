<?php

namespace App\Http\Resources\Rating;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RatingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rater_id' => $this->rater_id,
            'ratee_id' => $this->ratee_id,
            'rater' => new UserResource($this->whenLoaded('rater')),
            'ratee' => new UserResource($this->whenLoaded('ratee')),
            'rating' => $this->rating,
            'comment' => $this->comment,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
