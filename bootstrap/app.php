<?php

use App\Http\Middleware\Google2faMiddleware;
use App\Http\Middleware\JsonRequestMiddleware;
use App\Http\Middleware\LogRequestResponse;
use App\Listeners\MyBalanceUpdatedListener;
use Bavix\Wallet\Internal\Events\BalanceUpdatedEventInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            __DIR__ . '/../routes/web.php',
            __DIR__ . '/../routes/admin.php',
        ],
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: 'status',
    )
    ->withEvents([
        
    ])
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(
            prepend: [
                JsonRequestMiddleware::class,
            ]
        );
        $middleware->alias([
            'google2fa' => Google2faMiddleware::class,   
            "log_activity" => LogRequestResponse::class,         
        ]);
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
