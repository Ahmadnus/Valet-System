<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{

    use HasFactory;
protected $guarded = [];
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'chat_participants');
    }

    public static function createChat($agentId, $customerId)
    {
        $chat = self::create();
        $chat->participants()->attach([$agentId, $customerId]);
        return $chat;
    }
}

