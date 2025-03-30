<?php

namespace App\Console\Commands;

use App\Events\TruckingCar;
use Illuminate\Console\Command;

class BroadcastTrackingData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */   protected $signature = 'broadcast:tracking';


    protected $description = 'Broadcast tracking data every 3 seconds';


    public function handle()
    {
        $this->info('Starting to broadcast every 3 seconds...');


        while (true) {
            // Broadcast event here
            broadcast(new TruckingCar(['message' => 'Tracking data at: ' . now()]));


            $this->info('Data sent at: ' . now());


            sleep(3);
        }}
}
