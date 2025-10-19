<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // dashboard and admin route files are loaded inside web.php include below for clarity
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\ApplyUserScope::class,
            \App\Http\Middleware\TrackUserSession::class,
        ]);
        $middleware->alias([
            'ensure.subscribed' => \App\Http\Middleware\EnsureSubscribed::class,
            'enforce.plan' => \App\Http\Middleware\EnforcePlanLimits::class,
            'redirect.user.type' => \App\Http\Middleware\RedirectBasedOnUserType::class,
            'ensure.user.type' => \App\Http\Middleware\EnsureUserType::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
