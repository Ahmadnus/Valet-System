<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function requests()
{
    return $this->hasMany(Request::class);
}
public function host()
{
    return $this->belongsTo(User::class, 'host_id');
}

public function agents()
{
    return $this->belongsToMany(User::class, 'location_agent', 'location_id', 'agent_id');
}
public function parkingSlots()
{
    return $this->hasMany(ParkingSlot::class);
}
}
