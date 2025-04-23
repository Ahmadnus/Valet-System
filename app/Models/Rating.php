<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Rating extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function rater() {
        return $this->belongsTo(User::class, 'rater_id');
    }

    public function ratee() {
        return $this->belongsTo(User::class, 'ratee_id');
    }
}
