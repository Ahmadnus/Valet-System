<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LoyaltyPoint extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'points',
    ];

    /**
     * Get the user that owns the loyalty points.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function scopeForUser($query, $userId = null)
{
    return $query->where('user_id', $userId ?? Auth::id());
}
}
