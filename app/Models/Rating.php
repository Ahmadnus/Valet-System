<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = ['rater_id', 'ratee_id', 'rater_role', 'ratee_role', 'rating', 'comment'];

    // Relationship with Users
    public function rater() {
        return $this->belongsTo(User::class, 'rater_id');
    }

    public function ratee() {
        return $this->belongsTo(User::class, 'ratee_id');
    }
}
