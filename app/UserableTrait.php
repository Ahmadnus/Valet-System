<?php

namespace App;
use Spatie\Permission\Traits\HasRoles;
use App\Models\User;

trait UserableTrait
{
    public function user()
    {
        return $this->morphOne(User::class,'userable');
    }
}
