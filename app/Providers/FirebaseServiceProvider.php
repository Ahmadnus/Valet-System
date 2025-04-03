<?php

namespace App\Providers;
use Kreait\Firebase\Factory;
use Illuminate\Support\ServiceProvider;

class FirebaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('firebase.messaging', function () {
            $factory = (new Factory)->withServiceAccount(storage_path('firebase-config.json'));
            return $factory->createMessaging();
        });
    }
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
