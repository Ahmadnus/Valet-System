<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
    public function location()
{
    return $this->belongsTo(Location::class);
}
public function valetAgent()
{
    return $this->belongsTo(User::class, 'valet_agent_id');
}

public const STATUS_PENDING = 'pending';
public const STATUS_ACCEPTED = 'accepted';
public const STATUS_IN_PROGRESS = 'in_progress';
public const STATUS_PREPARING = 'preparing';
public const STATUS_COMPLETED = 'completed';
public const STATUS_CANCELED = 'canceled';
}
