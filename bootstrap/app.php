<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Http\Middleware\GlobalThrottleRequestsMiddleware::class);
        $middleware->append(\App\Http\Middleware\AssignRequestId::class);
        $middleware->append(\App\Http\Middleware\LogRequest::class);
        $middleware->append(\App\Http\Middleware\VerifySign::class);
        $middleware->validateCsrfTokens(except: [
            'stripe/*',
            '/*',
        ]);

    })

    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);




return $app;
