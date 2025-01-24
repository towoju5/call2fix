<?php

use App\Http\Controllers\BankController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CheckInOutController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DojaWebhookController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\FaqsController;
use App\Http\Controllers\Google2faController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MerchantController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServiceRequestRatingsController;
use App\Http\Controllers\SubAccountsController;
use App\Http\Controllers\TasksController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ServiceRequestController;
use Modules\Artisan\Http\Controllers\ArtisanController;
use Modules\Artisan\Http\Controllers\TaskController;
use Modules\ServiceProvider\Http\Controllers\ServiceProviderController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SettingsController;


Route::middleware(['api'])->domain(env('API_URL'))->prefix('v1')->group(function () {
    // Public routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('register-business', [AuthController::class, 'registerBis']);
        Route::post('login', [AuthController::class, 'login'])->name('login');
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('validate-reset-code', [AuthController::class, 'validateResetCode']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
        Route::post('social-login', [AuthController::class, 'socialLogin']);
        Route::post('validate-referrer-code', [AuthController::class, 'validateReferrer']);
    });

    // Protected routes 
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
        Route::get('settings/configs', [SettingsController::class, 'configs']);
        Route::get('user/{userId}', [AuthController::class, 'getUserById']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::delete('delete-account', [AuthController::class, 'deleteAccount']);
        Route::put('update-profile', [AuthController::class, 'updateProfile']);
        Route::post('verify-email', [AuthController::class, 'verifyEmail']);
        Route::put('update-password', [AuthController::class, 'updatePassword']);
        Route::post('business-profile', [AuthController::class, 'businessProfile']);


        Route::prefix('account-type')->group(function () {
            Route::get('/', [RoleController::class, 'getUserRoles']);
            Route::post('add', [RoleController::class, 'addRoleToUser']);
            Route::post('remove', [RoleController::class, 'removeRoleFromUser']);
        });

        // wallet routes
        Route::prefix('wallets')->middleware('log_activity')->group(function () {
            Route::get('/', [WalletController::class, 'getAllWallets']);
            Route::get('{walletType}', [WalletController::class, 'balance']);
            Route::post('{walletType}/deposit', [WalletController::class, 'deposit']);
            Route::post('{walletType}/withdraw', [WalletController::class, 'withdraw']);
            Route::post('transfer', [WalletController::class, 'transfer']);
            Route::post('new', [WalletController::class, 'addNewWallet']);
            Route::get('{walletType}/transactions', [WalletController::class, 'transactions']);
        });

        Route::prefix('banks')->group(function () {
            Route::get("/", [BankController::class, 'getBanks']);
            Route::post("validate-account", [BankController::class, 'validateAccountNumber']);
            Route::get('accounts', [WalletController::class, 'getBankAccount']);
            Route::get('accounts/{accountId}', [WalletController::class, 'getSingleBankAccount']);
            Route::post('accounts', [WalletController::class, 'addBankAccount']);
            Route::delete('accounts/{accountId}', [WalletController::class, 'deleteBankAccount']);
        });


        Route::get('top-products', [ProductController::class, 'topProducts']);
        Route::get('my-products', [ProductController::class, 'myProducts']);
        Route::apiResource('products', ProductController::class)->middleware('log_activity');
        Route::apiResource('property', PropertyController::class)->middleware('log_activity');
        Route::resource('categories', CategoryController::class);
        // Route::resource('departments', DepartmentController::class)->only(['store', 'orders', 'ServiceRequests']);


        Route::prefix('categories')->controller(CategoryController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('list/service-areas', 'service_areas');
            Route::get('show/{categoryId}', 'show');
            Route::get('service/{categoryId}', 'service');
        });

        Route::prefix('logs')->group(function () {
            Route::get('all', [EventsController::class, 'index']);
            Route::get('show/{eventId}', [EventsController::class, 'show']);
        });

        Route::prefix('ratings')->middleware('log_activity')->group(function () {
            Route::post('service-request-ratings', [ServiceRequestRatingsController::class, 'store']);
            Route::get('service-request-ratings/{id}', [ServiceRequestRatingsController::class, 'show']);
        });


        Route::prefix('services')->middleware('log_activity')->controller(ServiceRequestController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('request', 'store');
            Route::post('add-service-locations', 'addServiceLocations');
            Route::post('get-service-locations', 'getServiceLocations');
            Route::put('/update/{serviceRequestId}', 'update');
            Route::get('quotes/{requestId}', 'submittedQuotes');
            Route::get('quotes/{requestId}/{providerId}', 'submittedQuote');
            Route::get('providers', 'serviceProviders');
            Route::post('accept/{quoteId}/{requestId}', 'acceptQuote');
            Route::post('reject/{quoteId}/{requestId}', 'rejectQuote');
            Route::post('update-status/{requestId}', 'updateStatus');
            Route::post('update/{quoteId}/{requestId}/request', 'updateStatus');
            
            Route::get('negotiation/{requestId}/fetch', 'getNegotiation');
        });

        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::post('mark-as-read/{id}', [NotificationController::class, 'markAsRead']);
            Route::post('mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
            Route::delete('{id}', [NotificationController::class, 'destroy']);
            Route::delete('delete-all', [NotificationController::class, 'destroyAll']);
        });

        Route::middleware('log_activity')->group(function (){
            Route::apiResource('service-requests', ServiceRequestController::class);
            Route::get('service-requests/{serviceRequest}/featured-providers', [ServiceRequestController::class, 'getFeaturedProviders']);
            Route::get('service-requests/service-providers/all-requests', [ServiceRequestController::class, 'serviceProviderRequest']);
            Route::get('service-requests/{requestId}/clock', [CheckInOutController::class, 'clockins']);
            Route::put('service-requests/{requestId}/clock', [CheckInOutController::class, 'clock']);
            Route::put('service-requests/{requestId}/issueRework', [ServiceRequestController::class, 'issueRework']);
            
            Route::post('service-requests/negotiate/{negotiationId}', [ServiceRequestController::class, 'acceptNegotiatedPrice']);
            Route::post('service-requests/negotiate/{requestId}/{quoteId}', [ServiceRequestController::class, 'negotiateQuote']);
            Route::post('service-requests/make-payment/{requestId}/{walletType}', [ServiceRequestController::class, 'makePayment']);
        });

        Route::prefix('orders')->middleware('log_activity')->group(function () {
            Route::post('/new', [OrderController::class, 'place_order']);
            Route::post('calculate-shipping-rate', [OrderController::class, 'getShippingRate']);
            Route::get('/', [OrderController::class, 'getUserOrders']);
            Route::get('status/{status}', [OrderController::class, 'getOrdersByStatus']);
            Route::get('sorted', [OrderController::class, 'getSortedOrders']);
            Route::post('track', [OrderController::class, 'trackOrder']);
            Route::get('{id}', [OrderController::class, 'getOrder']);
            Route::get('{id}/accept-order', [OrderController::class, 'acceptOrder']);
            Route::get('{id}/reject-order', [OrderController::class, 'rejectOrder']);
            Route::put('{id}/cancel-order', [OrderController::class, 'cancelOrder']);
        });

        Route::prefix('artisans')->group(function () {
            Route::get('orders', [ArtisanController::class, 'index']);
            Route::get('tasks', [TaskController::class, 'index']);
            Route::get('requests', [ArtisanController::class, 'requests']);
            Route::post('submit-quote', [ArtisanController::class, 'submitQuote']);
            Route::post('{quoteId}/update-quote-status', [ArtisanController::class, 'updateQuoteStatus']);
            Route::get('quotes', [ArtisanController::class, 'quotes']);
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
            Route::post('upload/bulk', 'bulkUpload');
            Route::delete('{mediaPath}', 'destroy');
        });

        Route::prefix('chats')->group(function () {
            Route::get('/', [ChatController::class, 'index']);
            Route::post('/', [ChatController::class, 'store']);
            Route::get('{chat}', [ChatController::class, 'show']);
            Route::post('{chat}/messages', [ChatController::class, 'sendMessage']);
        });

        Route::prefix('plans')->group(function () {
            Route::get('/', [PlanController::class, 'index'])->name('plans.index');
            Route::get('{planId}/features', [PlanController::class, 'planFeatures'])->name('plans.features');
            Route::post('{planId}/subscribe', [PlanController::class, 'subscribe'])->name('plans.subscribe');
            Route::post('{planId}/change', [PlanController::class, 'changePlan'])->name('plans.change');
        });

        Route::group(['middleware' => ['auth', 'check.plan.limits']], function () {
            Route::post('subscribe', [MerchantController::class, 'subscribe']);
            Route::get('merchant-details', [MerchantController::class, 'getMerchantDetails']);
        });

        Route::prefix('accounts')->group(function () {
            Route::get('sub-accounts', [SubAccountsController::class, 'getSubAccounts'])->name('sub-accounts.index');
            Route::post('sub-accounts/add', [SubAccountsController::class, 'addSubAccount'])->name('sub-accounts.store');
            Route::post('sub-accounts/login/{subAccountId}', [SubAccountsController::class, 'loginSubAccount'])->name('sub-accounts.login');
            Route::get('sub-accounts/{subAccountId}', [SubAccountsController::class, 'fetchSubAccount'])->name('sub-accounts.show');
            Route::delete('sub-accounts/{subAccountId}', [SubAccountsController::class, 'deleteSubAccount'])->name('sub-accounts.destroy');
            Route::post('sub-accounts/{subAccountId}/fund', [SubAccountsController::class, 'fundSubAccount'])->name('sub-accounts.fund');
            Route::post('sub-accounts/{subAccountId}/transfer', [SubAccountsController::class, 'transferFromSubAccount'])->name('sub-accounts.transfer');
            Route::get('sub-accounts/{subAccountId}/balance', [SubAccountsController::class, 'getSubAccountBalance'])->name('sub-accounts.balance');
        });

        Route::prefix('tasks')->group(function () {
            Route::get('/', [TasksController::class, 'index']);
            Route::get('current-month-tasks', [TasksController::class, 'monthlyTasks']);
            Route::get('all-refers', [TasksController::class, 'allReferal']);
            Route::get('earning/history', [TasksController::class, 'allTimeEarnings']);
            Route::get('earning/transactions', [TasksController::class, 'earningsBreakdown']);
            Route::get('earning/claim/{taskId}', [TasksController::class, 'claimTaskEarnings']);
            
            Route::get('current-month-refers', [TasksController::class, 'monthlyReferrals']);
            Route::post('subscribe/{taskId}', [TasksController::class, 'subscribeToTask']);
            Route::get('affiliate/{affiliate}', [TasksController::class, 'getAffiliateTasks']);
            Route::put('update/{task}', [TasksController::class, 'updateAffiliateTask']);
            Route::get('check-status/{affiliate}', [TasksController::class, 'checkAffiliateStatus']);
            Route::post('calculate-commission/{serviceProvider}', [TasksController::class, 'calculateAffiliateCommission']);
            Route::put('update-activity/{affiliate}', [TasksController::class, 'updateAffiliateActivity']);
            Route::post('withdraw/{affiliate}', [TasksController::class, 'processAffiliateWithdrawal']);
        });

        Route::prefix('departments')->group(function () {
            Route::get('all', [DepartmentController::class, 'index'])->name('departments.list');
            Route::post('create', [DepartmentController::class, 'store'])->name('departments.store');
            Route::get('/{departmentId}/orders', [DepartmentController::class, 'orders'])->name('departments.orders');
            Route::get('/{departmentId}/wallet-history', [DepartmentController::class, 'walletHistory'])->name('departments.wallet-history');
            Route::get('/{departmentId}/service-requests', [DepartmentController::class, 'ServiceRequests'])->name('departments.serviceRequests');
        });

        Route::prefix('charts')->group(function () {
            Route::get('orders', [ChartController::class, 'orders'])->name('chart.orders');
            Route::get('service-requests', [ChartController::class, 'service_requests'])->name('chart.service_requests');
            Route::get('service-request-counts', [ChartController::class, 'service_request_counts'])->name('chart.service_request_counts');
            Route::get('suppliers-items-sold', [ChartController::class, 'suppliers_items_sold'])->name('chart.suppliers_items_sold');
            Route::get('products-count', [ChartController::class, 'products_count'])->name('chart.products_count');
            Route::get('wallets', [ChartController::class, 'wallets'])->name('chart.wallets');
            Route::get('wallet-transactions', [ChartController::class, 'wallet_transactions'])->name('chart.wallet_transactions');
        });
    });

    Route::apiResource('faqs', FaqsController::class);
    Route::post('support/email', [FaqsController::class, 'sendSupportEmail']);
});
