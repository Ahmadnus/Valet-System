<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarTypeTranslation extends Model
{
    use HasFactory;


    // Add this line to fix the table name
    public $table = 'cartype_translations';
    public $timestamps = false;
    public $fillable = ['type'];

}
