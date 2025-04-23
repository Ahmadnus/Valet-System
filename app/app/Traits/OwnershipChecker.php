<?php

namespace App\app\Traits;

use Illuminate\Support\Facades\Auth;

trait OwnershipChecker
{
    public function ensureUserOwnsModel($model)
    {
        if ($model->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
    }
}
