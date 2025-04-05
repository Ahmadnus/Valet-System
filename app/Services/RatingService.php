<?php
namespace App\Services;

use App\Models\Rating;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class RatingService
{
    public function rateAgent(array $data, int $agentId): Rating
    {
        return $this->createRating($agentId, $data, 'valet_customer', 'valet_agent');
    }

    public function rateCustomer(array $data, int $customerId): Rating
    {
        return $this->createRating($customerId, $data, 'valet_agent', 'valet_customer');
    }

    protected function createRating(int $rateeId, array $data, string $expectedRaterRole, string $expectedRateeRole): Rating
    {
        $rater = Auth::user();
        $ratee = User::findOrFail($rateeId);

        $raterRole = $rater->roles->first()->name ?? null;
        $rateeRole = $ratee->roles->first()->name ?? null;

        if ($raterRole !== $expectedRaterRole || $rateeRole !== $expectedRateeRole) {
            throw new Exception("Unauthorized role for rating.");
        }

        DB::beginTransaction();

        try {
            $rating = Rating::create([
                'rater_id' => $rater->id,
                'ratee_id' => $ratee->id,
                'rater_role' => $raterRole,
                'ratee_role' => $rateeRole,
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
            ]);

            DB::commit();
            Log::info("Rating created", ['rater_id' => $rater->id, 'ratee_id' => $ratee->id]);

            return $rating;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to create rating: " . $e->getMessage());
            throw new Exception("Error while creating rating.");
        }
    }
}
