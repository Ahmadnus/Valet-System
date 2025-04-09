<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Location extends Model implements TranslatableContract
{    use Translatable;
    use HasFactory;
    public $translatedAttributes = ['name'];

    protected $fillable = ['latitude', 'longitude', 'cost', 'host_id'];


    //
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
