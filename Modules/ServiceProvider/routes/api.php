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

Route::middleware(['auth:api'])->domain(env('API_URL'))->prefix('v1')->group(function () {
    Route::apiResource('serviceprovider', ServiceProviderController::class)->names('serviceprovider');

    // Route::get('provider/artisan', [ServiceProviderController::class, 'artisans'])->name('serviceprovider.artisans');
    Route::get('providers/featured/{propertyId}', [ServiceProviderController::class, 'getFeaturedProvider'])->name('serviceprovider.viewArtisan');
    Route::get('providers/artisan/{id}', [ServiceProviderController::class, 'viewArtisan'])->name('serviceprovider.viewArtisan');
    Route::delete('providers/artisan/{id}', [ServiceProviderController::class, 'deleteArtisan'])->name('serviceprovider.deleteArtisan');
    Route::post('providers/artisan', [ServiceProviderController::class, 'store'])->name('serviceprovider.storeArtisan');

});
