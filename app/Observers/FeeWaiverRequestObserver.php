<?php

namespace App\Observers;

use App\Models\FeeWaiverRequest;
use App\Models\ValetTime;
use App\Models\Request as UserRequest;
use App\NotificationType;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FeeWaiverRequestObserver
{
    /**
     * Handle the FeeWaiverRequest "created" event.
     */
    public function created(FeeWaiverRequest $feeWaiver)
    {
        $user=Auth::user();


        if ($user->loyaltypoints->points >= 100) {


            $feeWaiver->update([
                'status' => 'approved',
                'approved_by' => null
            ]);

            $userRequest = UserRequest::find($feeWaiver->request_id);
            $userRequest?->update(['fee_waived' => true]);

            $valetTime = ValetTime::where('user_id', $userRequest->user_id)->first();
            $valetTime?->update(['price' => 0]);

            $userRequest->payment()->create([
                'amount' => 0,
                'payment_method' => 'FREE',
                'status' => 'completed'
            ]);

            Log::info("Auto-approved fee waiver #{$feeWaiver->id} for user #{$user->id} (100+ loyalty points)");

            app(NotificationService::class)->sendNotification(
                title: 'Fee Waiver Approved',
                body: 'Your fee waiver has been approved automatically based on your loyalty points.',
                data: [
                    'request_id' => $feeWaiver->request_id,
                    'status' => 'approved',
                ],
                user: $feeWaiver->user,
                topic: \App\TopicType::VALET_CUSTOMER,
                type: NotificationType::ANSWER_WAIVE
            );
        }
    }

    /**
     * Handle the FeeWaiverRequest "updated" event.
     */
    public function updated(FeeWaiverRequest $feeWaiverRequest): void
    {
        //
    }

    /**
     * Handle the FeeWaiverRequest "deleted" event.
     */
    public function deleted(FeeWaiverRequest $feeWaiverRequest): void
    {
        //
    }

    /**
     * Handle the FeeWaiverRequest "restored" event.
     */
    public function restored(FeeWaiverRequest $feeWaiverRequest): void
    {
        //
    }

    /**
     * Handle the FeeWaiverRequest "force deleted" event.
     */
    public function forceDeleted(FeeWaiverRequest $feeWaiverRequest): void
    {
        //
    }
}
