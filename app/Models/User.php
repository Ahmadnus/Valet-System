<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\UserableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable , HasRoles ,HasApiTokens,UserableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function cars()
    {
        return $this->hasMany(Car::class);
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function notifications()
    {
        return $this->belongsToMany(Notification::class, 'notification_user')
                    ->withPivot('is_read')
                    ->withTimestamps();
    }
    public function valetTime()
    {
        return $this->hasMany(ValetTime::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
    public function userable()
    {
        return $this->morphTo('user');
    }
    public function locations()
{
    return $this->hasMany(AgentLocation::class, 'agent_id');
}

public function fcmTokens()
{
    return $this->hasMany(FcmToken::class);
}

public function hasCar($carId)
{
    dd();
    // Check if the user owns the car
    return $this->cars()->where('id', $carId)->exists();
}
public function loyaltyPoints()
{
    return $this->hasOne(LoyaltyPoint::class);
}

/**
 * Get the invoices for the user.
 */
public function invoices()
{
    return $this->hasMany(Invoice::class);
}

}
