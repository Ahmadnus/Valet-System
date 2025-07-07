<?php

namespace App\Traits\app\Traits;



use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait ChecksValetAgentRole
{
    public function ensureValetAgent(string $action = null)
    {
        $user = Auth::user();

        if (!$user || !$user->hasRole('valet_agent')) {
            Log::warning("Unauthorized valet_agent action" . ($action ? " during: $action" : '') . " by user " . optional($user)->id);
            throw new \Exception(trans('Unauthorized: You must be a valet agent to perform this action.'));
        }

        return $user;
    }
}
