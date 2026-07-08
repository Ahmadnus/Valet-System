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
        // Render a consistent JSON envelope for API requests and, crucially,
        // never leak internal exception messages/stack traces to clients for
        // unexpected errors when not in debug mode.
        $exceptions->render(function (\Throwable $e, $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null; // fall through to the default handler
            }

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], 422);
            }

            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }

            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json(['success' => false, 'message' => 'This action is unauthorized.'], 403);
            }

            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException
                || $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                return response()->json(['success' => false, 'message' => 'Resource not found.'], 404);
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'HTTP error.',
                ], $e->getStatusCode());
            }

            // Unexpected errors: log the detail, return a generic message
            // unless the app is explicitly in debug mode.
            report($e);

            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Server error.',
            ], 500);
        });
    })
    ->withSchedule(function(Schedule $schedule){
$schedule->command('parking:cancel-expired')->everyMinute()->withoutOverlapping();
$schedule->command('fee-waivers:auto-reject')->everyMinute()->withoutOverlapping();

    })
    ->create();
