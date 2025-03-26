<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValetTime extends Model
{
    use HasFactory;
    protected $guarded = [];



    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function locationAgent()
    {
        return $this->belongsTo(LocationAgent::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
