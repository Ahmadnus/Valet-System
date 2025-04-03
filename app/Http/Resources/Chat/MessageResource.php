<?php

namespace App\Http\Resources\Chat;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            // Ensuring proper relationship handling
            'chat' => $this->whenLoaded('chat', ($this->chat)),
            'user' => $this->whenLoaded('user', ($this->user)),

            'content' => $this->content,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
