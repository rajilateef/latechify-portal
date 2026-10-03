<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Monnify posts server-to-server; it's authenticated by its signature, not CSRF.
        // Monnify posts server-to-server; these are authenticated by signature, not CSRF.
        $middleware->validateCsrfTokens(except: [
            'webhooks/monnify',
            'summer-coding-camp/payment/webhook',
            'checkout/webhook',
        ]);

        $middleware->alias([
            'active.student' => \App\Http\Middleware\EnsureActiveStudent::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
