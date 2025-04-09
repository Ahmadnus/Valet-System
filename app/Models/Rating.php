<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
class Rating extends Model implements TranslatableContract
{   use Translatable;
    use HasFactory;
    public $translatedAttributes = ['comment'];
    protected $fillable = [
        'rater_id',
        'ratee_id',
        'rater_role',
        'ratee_role',
        'rating',

    ];
    // Relationship with Users
    public function rater() {
        return $this->belongsTo(User::class, 'rater_id');
    }

    public function ratee() {
        return $this->belongsTo(User::class, 'ratee_id');
    }
}
