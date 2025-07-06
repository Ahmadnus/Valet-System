<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingSlot extends Model
{
    use HasFactory;
    protected $fillable = [
        'location_id',
        'slot_number',
        'status',
    ];
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

}
