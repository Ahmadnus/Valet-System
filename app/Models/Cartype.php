<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Cartype extends Model implements HasMedia ,TranslatableContract

{
    use Translatable;
    use HasFactory ,InteractsWithMedia;

    public $translatedAttributes=['type'];
    public function cars()
    {
        return $this->morphMany(Car::class, 'cartypeable');
    }
}
