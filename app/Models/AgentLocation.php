<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentLocation extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
