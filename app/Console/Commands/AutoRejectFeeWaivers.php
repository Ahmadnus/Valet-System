<?php

namespace App\Console\Commands;
use App\Models\FeeWaiverRequest;
use App\Models\UserRequest;
use App\Models\ValetTime;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\NotificationType;
use App\Services\NotificationService;
use Carbon\Carbon;


use Illuminate\Console\Command;

class AutoRejectFeeWaivers extends Command
{
    protected $signature = 'fee-waivers:auto-reject';

    protected $description = 'Auto reject unhandled fee waiver requests after 5 minutes';

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    public function handle()
    {
        $cutoffTime = Carbon::now()->subMinutes(5);

        $feeWaivers = FeeWaiverRequest::where('status', 'pending')
            ->where('created_at', '<=', $cutoffTime)
            ->get();

        foreach ($feeWaivers as $feeWaiver) {
            DB::transaction(function () use ($feeWaiver) {
                $feeWaiver->update([
                    'status' => 'rejected',
                    'approved_by' => null,
                ]);

                Log::info("Fee waiver ID {$feeWaiver->id} auto-rejected after 5 minutes.");

                $this->notificationService->sendNotification(
                    title: 'Fee Waiver',
                    body: 'Your fee waiver request has been automatically rejected due to no response.',
                    data: [
                        'request_id' => $feeWaiver->request_id,
                        'status' => 'rejected',
                    ],
                    user: $feeWaiver->user,
                    topic: \App\TopicType::VALET_HOST,
                    type: NotificationType::ANSWER_WAIVE
                );
            });
        }

        $this->info('Checked and auto-rejected stale fee waiver requests.');
    }
}