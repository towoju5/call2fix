<?php

use Illuminate\Support\Facades\Route;
use Modules\Suppliers\Http\Controllers\SuppliersController;

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
    Route::apiResource('suppliers', SuppliersController::class)->names('suppliers');
    Route::put('order/status/update', [SuppliersController::class, 'updateOrder'])->name('order.status.update');
    Route::get('suppliers/histories/orders', [SuppliersController::class, 'orders'])->name('order.history.get');
});
