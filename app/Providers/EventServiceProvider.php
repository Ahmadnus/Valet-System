<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }
    protected $listen = [
        \App\Events\ParkingRequestCreated::class => [
            \App\Listeners\NotifyValetAgentsForParkingRequest::class,
        ],
        \App\Events\ParkingRequestAccepted::class => [
            \App\Listeners\SendParkingRequestAcceptedNotification::class,
        ],
        \App\Events\CarPreparationRequested::class => [
            \App\Listeners\SendCarPreparationNotification::class,
        ],
        \App\Events\ParkingRequestCanceled::class => [
            \App\Listeners\SendCancellationNotification::class,
        ],
        \App\Events\FeeWaiverRequested::class => [
            \App\Listeners\SendFeeWaiverRequestNotification::class,
        ],
       \App\Events\FeeWaiverHandled::class => [
           \App\Listeners\SendFeeWaiverHandledNotification::class,
        ],
    ];

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
