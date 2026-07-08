<?php

namespace App\Policies;

use App\Models\Request;
use App\Models\User;

class RequestPolicy
{
    /**
     * A manager may act on any request; otherwise the acting user must own it.
     */
    public function view(User $user, Request $request): bool
    {
        return $this->owns($user, $request);
    }

    public function pay(User $user, Request $request): bool
    {
        return $this->owns($user, $request);
    }

    private function owns(User $user, Request $request): bool
    {
        if (class_basename($user->userable_type) === 'Manager') {
            return true;
        }

        return $request->user_id === $user->id;
    }
}
