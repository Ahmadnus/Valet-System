<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class LocationTranslation extends Model
{

    use HasFactory;

    protected $table = 'location_translations';
    public $timestamps = false;
    public $fillable = ['name'];

}
