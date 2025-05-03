<?php
namespace App\Services;

use App\Models\Rating;
use App\Models\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use App\NotificationType;

class RatingService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

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


    $request = Request::findOrFail($requestId);


    if ($request->user_id !== $rater->id && $request->valet_agent_id !== $rater->id) {
        throw new Exception("You are not authorized to rate for this request.");
    }


    $rateeId = $request->user_id === $rater->id ? $request->valet_agent_id : $request->user_id;
    $ratee = User::findOrFail($rateeId);

    $raterRole = $rater->roles->first()->name ?? null;
    $rateeRole = $ratee->roles->first()->name ?? null;


    if ($raterRole !== $expectedRaterRole || $rateeRole !== $expectedRateeRole) {
        throw new Exception("Unauthorized role for rating.");
    }

    $existingCompletedRequest = Request::where([
        ['id', $requestId],
        ['status', 'completed']
    ])->first();

    if (!$existingCompletedRequest) {
        throw new \Exception("You can only rate a user after a completed request.");
    }


    $alreadyRated = Rating::where('rater_id', $rater->id)
        ->where('ratee_id', $ratee->id)
        ->where('request_id', $requestId)
        ->exists();

    if ($alreadyRated) {
        throw new \Exception(trans("You have already rated this user for this request."));
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
            'request_id'  => $requestId,
        ]);
        $tokens = $ratee->fcmTokens()->pluck('token')->toArray();


        if (!empty($tokens)) {
            $this->notificationService->sendMulticastNotification(
                title: trans('New Rating Received'),
                body: trans('You have received a new rating from a :role.', ['role' => $raterRole]),
                tokens: $tokens,
                data: [
                    'request_id' => $requestId,
                    'rating' => $data['rating'],
                ],
                type: NotificationType::NEW_VP_RATE
            );
            Log::info("Sent rating notification to user ID {$ratee->id}", [
                'tokens' => $tokens,
            ]);
        } else {
            Log::warning("User ID {$ratee->id} has no FCM tokens for rating notification.");
        }
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