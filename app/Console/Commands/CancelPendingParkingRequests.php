<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelPendingParkingRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parking:cancel-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $cutoffTime = now()->subMinutes(100);

        $requests = \App\Models\Request::where('status', 'pending')
            ->where('created_at', '<=', $cutoffTime)
            ->get();

        foreach ($requests as $request) {
            $request->update(['status' => 'canceled']);
            Log::info("Request ID {$request->id} has been auto-cancelled after 30 minutes.");
        }

        $this->info('Checked and cancelled old pending requests.');
    }
}
