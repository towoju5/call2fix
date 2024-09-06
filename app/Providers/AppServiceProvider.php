<?php

namespace App\Providers;

use App\Http\Middleware\JsonRequestMiddleware;
use Bavix\Wallet\Internal\Events\BalanceUpdatedEventInterface;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Event::listen(
        //     BalanceUpdatedEventInterface::class => [
        //         WalletUpdatedEventInterface::class,
        //     ]
        // );
    }
}
