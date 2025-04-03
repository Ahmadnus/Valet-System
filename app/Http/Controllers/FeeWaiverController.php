<?php

namespace App\Http\Controllers;

use App\Models\FeeWaiverRequest;
use App\Models\Request as UserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeeWaiverController extends Controller
{
    public function requestFeeWaiver(Request $request)
{
    $user = Auth::user();

    $feeWaiver = FeeWaiverRequest::create([
        'user_id' => $user->id,
        'request_id' => $request->input('request_id'),
        'status' => 'pending',
    ]);

    // Notify valet host & provider (use Firebase or Pusher)
    // Notification::send($valetHostsAndProviders, new FeeWaiverRequested($feeWaiver));

    return response()->json(['success' => true, 'message' => 'Fee waiver request sent.']);
}

public function handleFeeWaiver(Request $request, $id)
{
    $feeWaiver = FeeWaiverRequest::findOrFail($id);

    if ($feeWaiver->status !== 'pending') {
        return response()->json(['message' => 'This request has already been processed.'], 400);
    }

    $action = $request->input('action'); // 'approve' or 'reject'

    if (!in_array($action, ['approve', 'reject'])) {
        return response()->json(['error' => 'Invalid action. Use approve or reject.'], 400);
    }

    $status = $action === 'approve' ? 'approved' : 'rejected';

    $feeWaiver->update([
        'status' => $status,
        'approved_by' => Auth::id(),
    ]);

    if ($status === 'approved') {
        // Find the user request and update the fee_waived field
        $userRequest = UserRequest::findOrFail($feeWaiver->request_id);
        $userRequest->update(['fee_waived' => true]);

        // Also update the requests table (assuming UserRequest is related to requests)
        UserRequest::where('id', $userRequest->id)->update(['fee_waived' => true]);
    }

    return response()->json([
        'success' => true,
        'message' => "Fee waiver request {$status} successfully."
    ]);
}
}
