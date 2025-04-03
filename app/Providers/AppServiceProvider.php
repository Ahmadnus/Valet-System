<?php

namespace App\Providers;
use App\Models\Car;
use App\Models\Payment;
use App\Models\Request;
use App\Models\ValetTime;
use App\Observers\CarObserver;
use App\Observers\PaymentObserver;
use App\Observers\PriceObserver;
use App\Observers\ValetTimeObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Car::observe(CarObserver::class);
        Payment::observe(PaymentObserver::class);
        Request::observe(ValetTimeObserver::class);

    }
}
