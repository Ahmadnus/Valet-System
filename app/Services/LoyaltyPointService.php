<?php

namespace App\Services;

use App\app\traits\CheckRequestStatus;
use App\Models\LoyaltyPoint;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ValetTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class LoyaltyPointService
{
    use CheckRequestStatus;
    /**
     * Check user loyalty points
     */
    public function checkLoyaltyPoints()
    {
        $loyalty = LoyaltyPoint::forUser()->first();

        if (!$loyalty) {
            throw new Exception(trans("Loyalty points record not found."));
        }

        return $loyalty->points;
    }

    /**
     * Use loyalty points for a transaction
     */
    public function useLoyaltyPoints($points, $requestId)
    {
        $user = Auth::user();
        $this->checkRequestStatusInPreparing($requestId);

        DB::beginTransaction();

        $valetTime = ValetTime::where('user_id', $user->id)->first();
        $price = $valetTime->price ?? 0;

        $loyalty = LoyaltyPoint::forUser($user->id)->first();

        if (!$loyalty || $loyalty->points <= 0) {
            throw new Exception(trans("You don't have enough points."));
        }

        // Convert "all" to numeric value
        if ($points === 'all') {
            $points = $loyalty->points;
        }

        // Validate input is numeric and not more than available
        if (!is_numeric($points) || $points < 1 || $points > $loyalty->points) {
            throw new Exception(trans("Invalid points amount."));
        }

        // Determine how many points to actually use
        $maxUsablePoints = min($points, $price * 10); // 1 point = $0.10
        $discountAmount = $maxUsablePoints / 10;
        $remainingBalance = max(0, $price - $discountAmount);

        // Create invoice
        $invoice = Invoice::create([
            'user_id' => $user->id,
            'total_amount' => $price,
            'points_used' => $maxUsablePoints,
            'remaining_balance' => $remainingBalance,
        ]);

        // Update loyalty points and valet time
        $loyalty->points -= $maxUsablePoints;
        $loyalty->save();

        $valetTime->update(['price' => $remainingBalance]);

        // If balance is fully paid with points, create a completed payment
        if ($remainingBalance == 0) {
            Payment::create([
                'request_id'     => $requestId,
                'amount'         => $price,
                'status'         => 'completed',
                'payment_method' => 'points',
            ]);
        }

        DB::commit();

        return $invoice;
    }}

