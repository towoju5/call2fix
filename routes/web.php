<?php

use App\Events\MessageSent;
use App\Http\Controllers\Admin\ServiceAreaController;
use App\Http\Controllers\DojaWebhookController;
use App\Http\Controllers\FcmController;
use App\Http\Controllers\WebhookLogController;
use App\Http\Middleware\JsonRequestMiddleware;
use App\Models\Category;
use App\Models\ChatMessage;
use App\Models\Deposit;
use App\Models\Order;
use App\Models\Property;
use App\Models\User;
use Creatydev\Plans\Models\PlanModel;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Laravel\Telescope\Telescope;
use LaravelDaily\LaravelCharts\Classes\LaravelChart;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use function Pest\Laravel\withoutMiddleware;



Route::get('/', function () {
	return view('welcome');
});

Route::get('/fb', function () {
	// Read the SQL file content
	$sql = File::get(storage_path('app/fb.sql'));

	// Execute the SQL commands
	try {
		DB::unprepared($sql);
		return response()->json(['message' => 'Database restore completed successfully!'], 200);
	} catch (\Exception $e) {
		return response()->json(['error' => 'Error while restoring the database: ' . $e->getMessage()], 500);
	}
})->middleware(['auth'])->name('dashboard');


Route::get('clear', function () {
	Artisan::call('migrate');
	Artisan::call('route:clear');
	Artisan::call('cache:clear');
});

Route::post('logout', function () {
	auth('admin')->logout();
	return redirect()->to('https://call2fix.com.ng');
})->name('logout');


Route::any('send-sms', [DojaWebhookController::class, 'sendSMS']);

Route::put('api/v1/update-device-token', [FcmController::class, 'updateDeviceToken'])->withoutMiddleware(VerifyCsrfToken::class);
Route::post('api/v1/send-fcm-notification', [FcmController::class, 'sendFcmNotification'])->withoutMiddleware(VerifyCsrfToken::class);

Route::middleware(['auth:sanctum'])->domain(env('APP_URL'))->group(function () {
	Route::get('/', function () {
		return view('welcome');
	});
});


Route::middleware('admin')->group(function () {
	require_once('admin.php');
});


Route::fallback(function () {
	return get_error_response("API resource not found", [
		"error" => "API resource not found"
	], 404);
});

// Route::prefix('admin')->name('admin.')->group(function () {
//     Route::resource('service_areas', ServiceAreaController::class);
// });


Route::get('/chat/{friend}', function (User $friend) {
	return view('chat', [
		'friend' => $friend
	]);
})->middleware(['auth:sanctum'])->name('chat');


Route::post('/messages/{friend}', function (User $friend) {
	$message = ChatMessage::create([
		'sender_id' => auth()->id(),
		'receiver_id' => $friend->id,
		'text' => request()->input('message')
	]);

	broadcast(new MessageSent($message));

	return $message;
})->middleware(['auth:sanctum']);

Route::get('/messages/{friend}', function (User $friend) {
	return ChatMessage::query()
		->where(function ($query) use ($friend) {
			$query->where('sender_id', auth()->id())
				->where('receiver_id', $friend->id);
		})
		->orWhere(function ($query) use ($friend) {
			$query->where('sender_id', $friend->id)
				->where('receiver_id', auth()->id());
		})
		->with(['sender', 'receiver'])
		->orderBy('id', 'asc')
		->get();
})->middleware(['auth:sanctum']);


Route::withoutMiddleware(VerifyCsrfToken::class)->group(function () {
	Route::any('webhook/callback/paystack', [WebhookLogController::class, 'handleWebhook']);
});
