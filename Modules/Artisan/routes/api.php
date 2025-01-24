<?php

use Illuminate\Support\Facades\Route;
use Modules\Artisan\Http\Controllers\ArtisanController;

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
    Route::apiResource('artisan', ArtisanController::class)->names('artisan');


    Route::prefix('artisans')->controller(ArtisanController::class)->group(function(){
        Route::post('quote/create', 'addNewRequest');
    });
});
