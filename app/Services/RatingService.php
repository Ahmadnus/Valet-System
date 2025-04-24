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
    public function rateAgentByRequest(array $data, int $requestId): Rating
{
    return $this->handleRatingByRequest($requestId, $data, 'valet_customer', 'valet_agent');
}

public function rateCustomerByRequest(array $data, int $requestId): Rating
{
    return $this->handleRatingByRequest($requestId, $data, 'valet_agent', 'valet_customer');
}

protected function handleRatingByRequest(int $requestId, array $data, string $expectedRaterRole, string $expectedRateeRole): Rating
{
    $rater = Auth::user();

    // Fetch the request by its ID
    $request = Request::findOrFail($requestId);

    // Validate that the rater is part of the request
    if ($request->user_id !== $rater->id && $request->valet_agent_id !== $rater->id) {
        throw new Exception("You are not authorized to rate for this request.");
    }

    // Identify the ratee (the user being rated)
    $rateeId = $request->user_id === $rater->id ? $request->valet_agent_id : $request->user_id;
    $ratee = User::findOrFail($rateeId);

    $raterRole = $rater->roles->first()->name ?? null;
    $rateeRole = $ratee->roles->first()->name ?? null;

    // Ensure the roles match
    if ($raterRole !== $expectedRaterRole || $rateeRole !== $expectedRateeRole) {
        throw new Exception("Unauthorized role for rating.");
    }

    // Ensure the request has been completed
    $existingCompletedRequest = Request::where([
        ['id', $requestId],
        ['status', 'completed']
    ])->first();

    if (!$existingCompletedRequest) {
        throw new \Exception("You can only rate a user after a completed request.");
    }

    // Ensure the rater hasn't already rated this ratee for this request
    $alreadyRated = Rating::where('rater_id', $rater->id)
        ->where('ratee_id', $ratee->id)
        ->where('request_id', $requestId)
        ->exists();

    if ($alreadyRated) {
        throw new \Exception("You have already rated this user for this request.");
    }

    // Proceed with creating the rating
    DB::beginTransaction();

    try {
        $rating = Rating::create([
            'rater_id'    => $rater->id,
            'ratee_id'    => $ratee->id,
            'rater_role'  => $raterRole,
            'ratee_role'  => $rateeRole,
            'rating'      => $data['rating'],
            'comment'     => $data['comment'],
            'request_id'  => $requestId,  // Save the request_id in the rating
        ]);

        DB::commit();

        Log::info('Rating created', [
            'rater_id' => $rater->id,
            'ratee_id' => $ratee->id,
            'request_id' => $requestId
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