<?php

namespace App\Policies;

use App\Models\User;

class LoyaltyPolicy
{
    /**
     * Create a new policy instance.
     */
    public function checkOrUsePoints(User $user)
    {
        return $user->hasRole('customer') || $user->hasRole('manager');
    }
}
