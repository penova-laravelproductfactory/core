<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Every web request goes through Inertia's shared-props layer.
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        // Unauthenticated users are sent to the Core\Auth login page.
        $middleware->redirectGuestsTo(fn () => route('login'));

        // Authenticated users hitting guest pages (/login, /register)
        // go straight to the panel. Without this, Laravel's default
        // looks for a route literally named "dashboard", misses, and
        // bounces through "/" instead.
        $middleware->redirectUsersTo(fn () => route('penova.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
