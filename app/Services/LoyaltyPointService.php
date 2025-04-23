<?php

namespace App\Services;

use App\app\traits\CheckRequestStatus;
use App\Models\LoyaltyPoint;
use App\Models\Invoice;
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
            throw new Exception("Loyalty points record not found.");
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

        if (!$loyalty || $loyalty->points < $points) {
            throw new Exception("Not enough points.");
        }

        $loyalty->points -= $points;
        $loyalty->save();

        $remainingBalance = max(0, $price - ($points / 10));

        $invoice = Invoice::create([
            'user_id' => $user->id,
            'total_amount' => $price,
            'points_used' => $points,
            'remaining_balance' => $remainingBalance,
        ]);

        $valetTime->update(['price' => $remainingBalance]);

        DB::commit();

        return $invoice;
    }
}

