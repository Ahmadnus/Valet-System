<?php

namespace App\Services;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RatingService
{public function createRating($rateeId, $data)
    {
        $rater = Auth::user();
        $ratee = User::findOrFail($rateeId);

        $raterRole = $rater->roles->first()->name ?? null;
        $rateeRole = $ratee->roles->first()->name ?? null;



        if (!in_array($raterRole, ['valet_customer', 'valet_agent']) ||
            !in_array($rateeRole, ['valet_customer', 'valet_agent'])) {
            throw new \Exception('Invalid role assignment.');
        }


        if ($raterRole === $rateeRole) {
            throw new \Exception('You cannot rate someone with the same role.');
        }


        if ($rater->userable_type==basename('Customer')) {
            $rateeRole = 'valet_agent';
        } elseif ($raterRole === 'valet_agent') {
            $rateeRole = 'valet_customer';
        }


        return Rating::create([
            'rater_id' => $rater->id,
            'ratee_id' => $ratee->id,
            'rater_role' => $raterRole,
            'ratee_role' => $rateeRole,
            'rating' => $data['rating'],
        //    'comment' => $data['comment'],
        ]);
    }

}
