<?php

use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ServiceRequestController;


Route::middleware('api')->group(function () {
    // Public routes

    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('validate-reset-code', [AuthController::class, 'validateResetCode']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
        Route::post('social-login', [AuthController::class, 'socialLogin']);
    });

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::put('update-profile', [AuthController::class, 'updateProfile']);
        Route::post('verify-email', [AuthController::class, 'verifyEmail']);
        Route::put('update-password', [AuthController::class, 'updatePassword']);
        Route::post('business-profile', [AuthController::class, 'businessProfile']);

        // wallet routes
        Route::prefix('wallets')->group(function () {
            Route::get('/', [WalletController::class, 'getAllWallets']);
            Route::get('{walletType}/balance', [WalletController::class, 'balance']);
            Route::post('{walletType}/deposit', [WalletController::class, 'deposit']);
            Route::post('{walletType}/withdraw', [WalletController::class, 'withdraw']);
            Route::post('transfer', [WalletController::class, 'transfer']);
            Route::get('{walletType}/transactions', [WalletController::class, 'transactions']);
        });


        Route::apiResource('products', ProductController::class);
        Route::apiResource('marketplace', MarketplaceController::class);
        Route::prefix('marketplace')->controller(MarketplaceController::class)->group(function () {
            Route::get('browseItems', 'browseItems');
            Route::post('purchaseItem', 'purchaseItem');
            Route::post('payForProduct', 'payForProduct');
            Route::post('trackOrder', 'trackOrder');
        });


        Route::apiResource('service-requests', ServiceRequestController::class);
        Route::get('service-requests/{serviceRequest}/featured-providers', [ServiceRequestController::class, 'getFeaturedProviders']);
    });

});
