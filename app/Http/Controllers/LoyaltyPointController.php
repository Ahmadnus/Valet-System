<?php

namespace App\Http\Controllers;
use App\Models\Manager;
use App\Http\Requests\StoreManagerRequest;
use App\Http\Requests\UpdateManagerRequest;
use App\Http\Requests\Manager\RoleCountRequest;
use App\Http\Resources\ٌRequest as UserResource;
use App\Models\Agent;
use App\Models\Customer;
use App\Models\Host;
use App\Models\Invoice;
use App\Models\LoyaltyPoint;
use App\Models\Provider;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\ValetTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Exception;



class LoyaltyPointController extends Controller
{
    public function checkLoyaltyPoints(User $user)
{
    $userId=Auth::user()->id;
    $loyalty = LoyaltyPoint::where('user_id', $userId)->first();

    return response()->json([
        'success' => true,
        'points' => $loyalty->points ,
    ]);
}
public function useLoyaltyPoints(Request $request)
{
    $user = Auth::user();
    $valetTime = ValetTime::where('user_id', $user->id)->first();
    $price  = ValetTime::where('user_id', $user->id)->value('price');

    $points = $request->input('points'); // Amount of points user wants to use


    $loyalty = LoyaltyPoint::where('user_id', $user->id)->first();

    if ($loyalty && $loyalty->points >= $points) {
        // Deduct points
        $loyalty->points -= $points;
        $loyalty->save();

        // Calculate remaining balance after using points
        $remainingBalance = max(0, $price  - ($points / 10)); // Assuming 1 point = $0.1

        // Create an invoice
        $invoice = Invoice::create([
            'user_id' => $user->id,
            'total_amount' => $price ,
            'points_used' => $points,
            'remaining_balance' => $remainingBalance,
        ]);
        $valetTime->update([
            'price' => $remainingBalance, // Update price with remaining balance
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Points applied successfully',
            'invoice' => $invoice,
        ]);
    }

    return response()->json(['error' => 'Not enough points'], 400);
}

}
