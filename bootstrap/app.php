<?php

use App\Http\Middleware\CheakUser;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'platform_manager' => \App\Http\Middleware\PlatformManagerMiddleware::class,
            'check.role' => \App\Http\Middleware\CheckUserRole::class,
            'check.type' => \App\Http\Middleware\CheckUserType::class,
            'check.user.allowed' => \App\Http\Middleware\CheckIfCustomerAgentManager::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function(Schedule $schedule){
$schedule->command('parking:cancel-expired')->everySecond();
$schedule->command('fee-waivers:auto-reject')->everySecond();

    })
    ->create();
