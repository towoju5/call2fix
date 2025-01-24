<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\KwikDeliveryController;
use App\Http\Controllers\Admin\OrdersController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\PlansController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ServiceAreaController;
use App\Http\Controllers\Admin\ServiceRequestController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SubscriptionsController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UsersController;




Route::domain(env('ADMIN_URL'))->group(function () {
    Route::get('/', function () {
        return redirect()->to(env('APP_URL'));
    });

    Route::get('admin/login', function () {
        return view('login');
    })->name('admin.login');

    Route::post('admin/login/process', [AdminController::class, 'login'])->name('admin.login.submit');
    Route::get(env('TELESCOPE_PATH'))->name('admin.api.logs');
    Route::middleware('auth:admin')->prefix('cp')->as('admin.')->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::group(['prefix' => 'manage-admin'], function () {
            Route::resource('roles', RoleController::class);
            Route::resource('admins', AdminController::class);
            Route::resource('permissions', PermissionsController::class);
            Route::resource('settings', SettingsController::class);
            Route::post('admins/{admin}/assign-super-admin', [AdminController::class, 'assignSuperAdmin'])->name('assign-super-admin');
        });

        Route::resource('service_areas', ServiceAreaController::class)->names('service_areas');
        Route::resource('categories', CategoryController::class)->names('categories');
        Route::get('categories/{category}/services', [CategoryController::class, 'showServices'])->name('categories.services');
        Route::resource('users', UsersController::class)->names('users');
        Route::resource('properties', PropertyController::class)->names('properties');
        Route::resource('plans', PlansController::class);
        Route::resource('subscriptions', SubscriptionsController::class);
        Route::resource('kwik-delivery', KwikDeliveryController::class);

        Route::prefix('')->group(function () {
            Route::resource('orders', OrdersController::class)->except(['create', 'edit']);
            Route::post('orders/{order}/status', [OrdersController::class, 'updateStatus'])->name('orders.updateStatus');
            Route::get('orders/{order}/track', [OrdersController::class, 'trackOrder'])->name('orders.track');
        });

        

        Route::group(['prefix' => 'service-requests', 'as' => 'service-requests.'], function () {
            Route::get('/', [ServiceRequestController::class, 'index'])->name('index');
            Route::get('/create', [ServiceRequestController::class, 'create'])->name('create');
            Route::post('/', [ServiceRequestController::class, 'store'])->name('store');
            Route::get('/{serviceRequest}', [ServiceRequestController::class, 'show'])->name('show');
            Route::get('/{serviceRequest}/edit', [ServiceRequestController::class, 'edit'])->name('edit');
            Route::put('/{serviceRequest}', [ServiceRequestController::class, 'update'])->name('update');
            Route::delete('/{serviceRequest}', [ServiceRequestController::class, 'destroy'])->name('destroy');

            Route::get('/create-on-behalf', [ServiceRequestController::class, 'createOnBehalfOfCustomer'])->name('create-on-behalf');
            Route::post('/store-on-behalf', [ServiceRequestController::class, 'storeOnBehalfOfCustomer'])->name('store-on-behalf');
        });

        Route::group(['prefix' => 'products', 'as' => 'products.'], function () {
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::get('/create', [ProductController::class, 'create'])->name('create');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::get('/{product}', [ProductController::class, 'show'])->name('show');
            Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
            Route::put('/{product}', [ProductController::class, 'update'])->name('update');
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
            Route::post('order', [ProductController::class, 'order'])->name('products.order');

        });

        Route::prefix('users')->group(function () {
            Route::get('/', [UsersController::class, 'index'])->name('users.index');
            Route::get('{user}', [UsersController::class, 'show'])->name('users.show');
            Route::patch('{user}/ban', [UsersController::class, 'ban'])->name('users.ban');
            Route::patch('{user}/unban', [UsersController::class, 'unban'])->name('users.unban');
            Route::post('/', [UsersController::class, 'store'])->name('user.store');
            Route::put('{user}/', [UsersController::class, 'update'])->name('user.update');
            Route::post('topup/{userId}', [UsersController::class, 'topUpWallet'])->name('users.topup');
            Route::post('debit/{userId}', [UsersController::class, 'debitWallet'])->name('users.debit');
            Route::post('import', [UsersController::class, 'import'])->name('users.import');
            Route::post('{is}/destroy', [UsersController::class, 'destroy'])->name('users.destroy');

            Route::get('{user}/transactions', [UsersController::class, 'getTransactions'])->name('users.transactions');
            Route::get('{user}/service-requests', [UsersController::class, 'getServiceRequests'])->name('users.service-requests');
            Route::get('{user}/products', [UsersController::class, 'getProducts'])->name('users.products');
            Route::get('{user}/orders', [UsersController::class, 'getOrders'])->name('users.orders');
        });

        Route::post('logout', [AdminController::class, 'logout'])->name('logout');
    });
});
