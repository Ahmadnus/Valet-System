<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationAgent extends Model
{
    use HasFactory;
    protected $table = 'location_agent';


    protected $fillable = [
        'location_id',
        'agent_id',
        'status',
    ];


}
