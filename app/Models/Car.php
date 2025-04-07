<?php

namespace App\Models;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model implements HasMedia
{
    use HasFactory,InteractsWithMedia;

    protected $fillable = ['user_id', 'plate_number', 'key_box', 'cartypeable_id', 'cartypeable_type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function cartypeable()
    {
        return $this->morphTo();
    }

}
