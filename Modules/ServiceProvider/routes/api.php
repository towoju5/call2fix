<?php

use Illuminate\Support\Facades\Route;
use Modules\ServiceProvider\Http\Controllers\ServiceProviderController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
 */

Route::middleware(['auth:sanctum'])->domain(env('API_URL'))->prefix('v1')->group(function () {
    Route::apiResource('serviceprovider', ServiceProviderController::class)->names('serviceprovider');
    
    Route::get('provider/artisan', [ServiceProviderController::class, 'artisans'])->name('serviceprovider.artisans');
    Route::get('providers/artisan/{id}', [ServiceProviderController::class, 'viewArtisan'])->name('serviceprovider.viewArtisan');
    Route::get('providers/featured/{propertyId}', [ServiceProviderController::class, 'getFeaturedProvider'])->name('serviceprovider.property.viewArtisan');
    Route::delete('providers/delete-artisan/{id}', [ServiceProviderController::class, 'deleteArtisan'])->name('serviceprovider.deleteArtisan');
    Route::post('providers/artisan', [ServiceProviderController::class, 'addArtisan'])->name('serviceprovider.storeArtisan');
    Route::post('providers/artisan/{artisanId}', [ServiceProviderController::class, 'updateArtisan'])->name('serviceprovider.updateArtisan');

    Route::prefix('providers')->group(function () {
        Route::get('artisans', [ServiceProviderController::class, 'artisans']);
        
        Route::get('provider-address', [ServiceProviderController::class, 'provider_address']);
        Route::get('providers-category', [ServiceProviderController::class, 'providers_category']);
        
        Route::get('quotes', [ServiceProviderController::class, 'getQuotes']);
        Route::get('artisan/quotes/histories', [ServiceProviderController::class, 'artisanQuotes']);
        Route::get('artisan/quotes/history/{requestID}', [ServiceProviderController::class, 'artisanQuote']);
        Route::get('requests', [ServiceProviderController::class, 'requests']);
        Route::post('submit-quote', [ServiceProviderController::class, 'submitQuote']);
        Route::post('invite-artisan', [ServiceProviderController::class, 'addArtisanToRequest']);
        Route::get("artisans/{artisanId}", [ServiceProviderController::class, 'viewArtisan']);
        Route::delete("artisan/{artisanId}", [ServiceProviderController::class, 'deleteArtisan']);
    });
});
