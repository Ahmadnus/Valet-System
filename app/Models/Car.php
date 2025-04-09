<?php

namespace App\Models;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model implements HasMedia
{
    use HasFactory,InteractsWithMedia;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function cartypeable()
    {
        return $this->morphTo();
    }

}
