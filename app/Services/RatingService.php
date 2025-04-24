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

    $completedRequest = \App\Models\Request::where(function ($query) use ($rater, $ratee) {
        $query->where('user_id', $rater->id)
              ->where('valet_agent_id', $ratee->id);
    })
    ->orWhere(function ($query) use ($rater, $ratee) {
        $query->where('user_id', $ratee->id)
              ->where('valet_agent_id', $rater->id);
    })
    ->where('status', 'completed')
    ->latest()
    ->first();

    if (!$completedRequest) {
        throw new \Exception("You can only rate a user after a completed request between you.");
    }

    $alreadyRated = \App\Models\Rating::where('rater_id', $rater->id)
        ->where('ratee_id', $ratee->id)
        ->where('request_id', $completedRequest->id)
        ->exists();

    if ($alreadyRated) {
        throw new \Exception("You have already rated this user for this request.");
    }

    DB::beginTransaction();

    try {
        $rating = Rating::create([
            'rater_id'    => $rater->id,
            'ratee_id'    => $ratee->id,
            'request_id'  => $completedRequest->id,
            'rater_role'  => $raterRole,
            'ratee_role'  => $rateeRole,
            'rating'      => $data['rating'],
            'comment'     => $data['comment'],
        ]);

        DB::commit();

        Log::info('Rating created', [
            'rater_id' => $rater->id,
            'ratee_id' => $ratee->id,
            'request_id' => $completedRequest->id,
        ]);

        return $rating;
    } catch (\Exception $e) {
        DB::rollBack();

        Log::error('Failed to create rating: ' . $e->getMessage());
        throw new Exception("Error while creating rating.");
    }
}}
