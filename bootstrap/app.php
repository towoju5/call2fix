<?php

use App\Http\Middleware\JsonRequestMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: 'status',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(
            prepend: [
                JsonRequestMiddleware::class,
            ]
        );
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(
            fn(AuthenticationException $exception, $request) => get_error_response(
                'Unauthenticated',
                ['error' => 'Unauthenticated'],
                401
            )
        );
    })->create();
