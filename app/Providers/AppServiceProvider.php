<?php

namespace App\Providers;

use App\Http\Middleware\JsonRequestMiddleware;
use Bavix\Wallet\Internal\Events\BalanceUpdatedEventInterface;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Models\Order;
use App\Models\Property;
use App\Models\ServiceRequest;
use App\Models\Wallet;
use App\Observers\OrderModelObserver;
use App\Observers\PropertyModelObserver;
use App\Observers\ServiceRequestModelObserver;
use App\Observers\WalletModelObserver;
use Towoju5\Wallet\Models\Wallet as ModelsWallet;

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
        Order::observe(OrderModelObserver::class);
        Property::observe(PropertyModelObserver::class);
        ServiceRequest::observe(ServiceRequestModelObserver::class);
        Wallet::observe(WalletModelObserver::class);
        // ModelsWallet::observe(WalletModelObserver::class);
        // Event::listen(
        //     BalanceUpdatedEventInterface::class => [
        //         WalletUpdatedEventInterface::class,
        //     ]
        // );
    }
}
