<?php

namespace App\Console\Commands;

use App\Services\Requests\ParkingRequestService;
use Illuminate\Console\Command;

class CancelExpiredRequests extends Command
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
    protected $description = 'Automatically cancel parking requests older than 1 hour';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        app(ParkingRequestService::class)->cancelExpiredRequests();

        $this->info('Expired parking requests checked.');
    }
}
