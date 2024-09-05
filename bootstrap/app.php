<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
->withRouting(
    function () {
        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('routes/api.php'));
        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    },
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'api/auth/login',
            'api/auth/logout',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
