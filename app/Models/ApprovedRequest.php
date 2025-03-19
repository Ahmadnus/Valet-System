<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovedRequest extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'plate_number', 'key_box', 'cartypeable_id', 'cartypeable_type', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
