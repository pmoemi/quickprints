<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'bms/api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->alias([
            'bms.page' => \App\Http\Middleware\EnsureBmsAccess::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('bms.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
