<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: base_path('app/Presentation/Routes/web.php'),
        api: base_path('app/Presentation/Routes/api.php'),
        commands: base_path('routes/console.php'),
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middleware Jika Web Iddle Selama 30 Menit, Maka Logout
        $middleware->alias([
            'idle.timeout' => \App\Http\Middleware\AuthenticateSessionIdleTimeout::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

    $middleware->alias([
        'idle.timeout' => \App\Http\Middleware\AuthenticateSessionIdleTimeout::class,
    ]);