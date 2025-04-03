<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'total_amount',
        'points_used',
        'remaining_balance',
    ];

    /**
     * Get the user that owns the invoice.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * You can add a relation if you need to track loyalty points used.
     */
    public function loyaltyPoints()
    {
        return $this->belongsTo(LoyaltyPoint::class);
    }
}
