<?php

use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\LogUserAgent;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: 'api',
    )
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('app:update-event-status')->daily()->withoutOverlapping();
    })
    ->withMiddleware(function (Middleware $middleware) {
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
