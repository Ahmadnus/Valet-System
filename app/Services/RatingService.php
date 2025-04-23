<?php
namespace App\Services;

use App\Models\Rating;
use App\Models\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class RatingService
{
    public function rateAgent(array $data, int $agentId): Rating
    {
        return $this->handleRating($agentId, $data, 'valet_customer', 'valet_agent');
    }

    public function rateCustomer(array $data, int $customerId): Rating
    {
        return $this->handleRating($customerId, $data, 'valet_agent', 'valet_customer');
    }

    protected function handleRating(int $rateeId, array $data, string $expectedRaterRole, string $expectedRateeRole): Rating
    {
        $rater = Auth::user();
        $ratee = User::findOrFail($rateeId);

        $raterRole = $rater->roles->first()->name ?? null;
        $rateeRole = $ratee->roles->first()->name ?? null;

        if ($raterRole !== $expectedRaterRole || $rateeRole !== $expectedRateeRole) {
            throw new Exception("Unauthorized role for rating.");
        }

  
        $existingCompletedRequest = \App\Models\Request::where([
            ['user_id', $rater->id],
            ['valet_agent_id', $ratee->id],
            ['status', 'completed']
        ])
        ->orWhere([
            ['user_id', $ratee->id],
            ['valet_agent_id', $rater->id],
            ['status', 'completed']
        ])
        ->first();
    if (!$existingCompletedRequest) {
    throw new \Exception("You can only rate a user after a completed request between you.");
}

$alreadyRated = \App\Models\Rating::where('rater_id', $rater->id)
    ->where('ratee_id', $ratee->id)
    ->exists();

if ($alreadyRated) {
    throw new \Exception("You have already rated this user.");
}

        DB::beginTransaction();

        try {
            $rating = Rating::create([
                'rater_id'    => $rater->id,
                'ratee_id'    => $ratee->id,
                'rater_role'  => $raterRole,
                'ratee_role'  => $rateeRole,
                'rating'      => $data['rating'],
                'comment'     => $data['comment'],
            ]);

            DB::commit();

            Log::info('Rating created', [
                'rater_id' => $rater->id,
                'ratee_id' => $ratee->id,
            ]);

            return $rating;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create rating: ' . $e->getMessage());
            throw new Exception("Error while creating rating.");
        }

    }

    public function getAllAgentRatings()
    {
        return $this->getAllRatingsByRole('valet_agent');
    }

    public function getAllCustomerRatings()
    {
        return $this->getAllRatingsByRole('valet_customer');
    }

    protected function getAllRatingsByRole(string $role)
    {
        return Rating::with(['rater', 'ratee'])
            ->where('ratee_role', $role)
            ->latest()
            ->get();
    }
}
