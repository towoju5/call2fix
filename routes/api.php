<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\Google2faController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ServiceRequestController;
use Modules\Artisan\Http\Controllers\ArtisanController;
use Modules\ServiceProvider\Http\Controllers\ServiceProviderController;


Route::middleware(['api'])->domain(env('API_URL'))->prefix('v1')->group(function () {
    // Public routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login'])->name('login');
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('validate-reset-code', [AuthController::class, 'validateResetCode']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
        Route::post('social-login', [AuthController::class, 'socialLogin']);
    });

    // Protected routes 
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::put('update-profile', [AuthController::class, 'updateProfile']);
        Route::post('verify-email', [AuthController::class, 'verifyEmail']);
        Route::put('update-password', [AuthController::class, 'updatePassword']);
        Route::post('business-profile', [AuthController::class, 'businessProfile']);

        // wallet routes
        Route::prefix('wallets')->group(function () {
            Route::get('/', [WalletController::class, 'getAllWallets']);
            Route::get('{walletType}', [WalletController::class, 'balance']);
            Route::post('{walletType}/deposit', [WalletController::class, 'deposit']);
            Route::post('{walletType}/withdraw', [WalletController::class, 'withdraw']);
            Route::post('transfer', [WalletController::class, 'transfer']);
            Route::post('new', [WalletController::class, 'addNewWallet']);
            Route::get('{walletType}/transactions', [WalletController::class, 'transactions']);
        });


        Route::apiResource('products', ProductController::class);
        Route::apiResource('property', PropertyController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('departments', DepartmentController::class)->only(['store']);



        Route::prefix('categories')->controller(CategoryController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('list/service-areas', 'service_areas');
            Route::get('show/{categoryId}', 'show');
            Route::get('service/{categoryId}', 'service');
        });

        // Route::prefix('marketplace')->group(function () {
        //     Route::get('browse', [MarketplaceController::class, 'browseItems']);
        //     Route::get('product/{productId}', [MarketplaceController::class, 'ItemDetails']);
        //     Route::post('purchase', [MarketplaceController::class, 'purchaseItem']);
        //     Route::post('request', [MarketplaceController::class, 'requestItem']);
        //     Route::post('pay', [MarketplaceController::class, 'payForProduct']);
        //     Route::get('track', [MarketplaceController::class, 'trackOrder']);
        //     Route::post('sell', [MarketplaceController::class, 'sellItem']);
        // });

        Route::prefix('services')->controller(ServiceRequestController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('request', 'store');
            Route::put('/update/{serviceRequestId}', 'update');
            Route::get('providers', 'serviceProviders');
        });

        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::post('mark-as-read/{id}', [NotificationController::class, 'markAsRead']);
            Route::post('mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
            Route::delete('{id}', [NotificationController::class, 'destroy']);
            Route::delete('delete-all', [NotificationController::class, 'destroyAll']);
        });

        Route::apiResource('service-requests', ServiceRequestController::class);
        Route::get('service-requests/{serviceRequest}/featured-providers', [ServiceRequestController::class, 'getFeaturedProviders']);

        Route::prefix('orders')->group(function () {
            Route::post('/new', [OrderController::class, 'place_order']);
            Route::get('/', [OrderController::class, 'getUserOrders']);
            Route::get('{id}', [OrderController::class, 'getOrderStatus']);
            Route::get('status/{status}', [OrderController::class, 'getOrdersByStatus']);
            Route::get('sorted', [OrderController::class, 'getSortedOrders']);
        });

        Route::prefix('artisans')->group(function () {
            Route::get('orders', [ArtisanController::class, 'index']);
            Route::get('requests', [ArtisanController::class, 'requests']);
            Route::post('submit-quote', [ArtisanController::class, 'submitQuote']);
            Route::post('{quoteId}/update-quote-status', [ArtisanController::class, 'updateQuoteStatus']);
            Route::get('quotes', [ArtisanController::class, 'quotes']);
        });

        Route::prefix('providers')->group(function () {
            Route::get('artisans', [ServiceProviderController::class, 'artisans']);
            Route::get('artisan/quotes', [ServiceProviderController::class, 'artisanQuotes']);
            Route::get('requests', [ServiceProviderController::class, 'requests']);
            Route::post('submit-quote', [ServiceProviderController::class, 'submitQuote']);
            Route::get("artisan/{artisanId}", [ServiceProviderController::class, 'viewArtisan']);
            Route::delete("artisan/{artisanId}", [ServiceProviderController::class, 'deleteArtisan']);
        });


        Route::group(['middleware' => 'google2fa'], function () {
            Route::post('generate-2fa-secret', [Google2faController::class, 'generateSecret']);
            Route::post('enable-2fa', [Google2faController::class, 'enable2fa']);
            Route::post('verify-2fa', [Google2faController::class, 'verify2fa']);
            Route::post('disable-2fa', [Google2faController::class, 'disable2fa']);
        });

        Route::prefix('media/file/')->controller(MediaController::class)->group(function () {
            Route::get('{mediaPath}', 'fetch');
            Route::post('upload', 'upload');
            Route::delete('{mediaPath}', 'destroy');
        });


        Route::prefix('chats')->group(function () {
            Route::get('/', [ChatController::class, 'index']);
            Route::post('/', [ChatController::class, 'store']);
            Route::get('{chat}', [ChatController::class, 'show']);
            Route::post('{chat}/messages', [ChatController::class, 'sendMessage']);
        });

    });

});
