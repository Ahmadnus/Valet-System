<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'car_id',
        'parking_slot_id',
        'location_id',
        'status',
        'fee_waived',
        'valet_agent_id',
        'total_hours',
        'in_progress_time',
        'completed_time',
        'customer_latitude',
        'customer_longitude',
        'requested_by_user',
    ];

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
public function valete()
{
    return $this->hasMany(User::class, 'valit');
}
public function valetTime()
{
    return $this->hasMany(ValetTime::class);
}
public function payment()
{
    return $this->hasOne(Payment::class);
}
public function parkingSlot()
{
    return $this->belongsTo(ParkingSlot::class, 'parking_slot_id');
}


public const STATUS_PENDING = 'pending';
public const STATUS_ACCEPTED = 'accepted';
public const STATUS_IN_PROGRESS = 'in_progress';
public const STATUS_PREPARING = 'preparing';
public const STATUS_DELIVERING = 'delivering';
public const STATUS_COMPLETED = 'completed';
public const STATUS_CANCELED = 'canceled';
protected $casts = [
    'completed_time' => 'datetime',
    'in_progress_time' => 'datetime',
];

public function calculateTimeDifference()
{
    // Ensure both timestamps exist before calculating
    if (empty($this->completed_time) || empty($this->in_progress_time)) {
        return 0; // Return 0 if any timestamp is missing
    }
  // Convert to Carbon instances if not already
  $completedTime = Carbon::parse($this->completed_time);
  $inProgressTime = Carbon::parse($this->in_progress_time);

    return abs($this->completed_time->diffInHours($this->in_progress_time));
}


public function scopeOwnedByUser($query, ?int $userId = null)
{
    return $query->where('user_id', $userId ?? Auth::id());
}
}
