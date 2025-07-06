<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'request_id',
        'amount',
        'status',
        'payment_method',
        'data',
    ];


// In the Payment model
public function user()
{
    return $this->belongsTo(User::class);
}
public function request()
{
    return $this->belongsTo(Request::class, 'request_id');
}
}
