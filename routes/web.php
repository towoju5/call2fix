<?php

use App\Events\MessageSent;
use App\Http\Controllers\Admin\ServiceAreaController;
use App\Http\Controllers\WebhookLogController;
use App\Http\Middleware\JsonRequestMiddleware;
use App\Models\Category;
use App\Models\ChatMessage;
use App\Models\Deposit;
use App\Models\Order;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Laravel\Telescope\Telescope;
use LaravelDaily\LaravelCharts\Classes\LaravelChart;
use Spatie\Permission\Models\Role;



Route::get('gee', function () {
	$baseUrl = "https://bitso.com";
	$requestPath = "/api/v3/funding_details/pse/payment_links";
	$apiKey = "AUFEXQubph";
	$apiSecret = "115cdcaab9cc969acc2b0d70eb813635";
	$nonce = round(microtime(true) * 1000); // Current time in milliseconds

	$data = [
		"amount" => 1000,
		"cellphone" => "+573103922790",
		"email" => "towojuads@gmail.com",
		"document_type" => "CC",
		"document_number" => "1053851282",
		"full_name" => "Daniela Aldana Valencia",
	];

	$JSONPayload = json_encode($data);
	$signatureData = $nonce . 'POST' . $requestPath . $JSONPayload;
	$signature = hash_hmac('sha256', $signatureData, $apiSecret);
	$authHeader = "Bitso $apiKey:$nonce:$signature";
	$url = $baseUrl . $requestPath;

	// Log request data
	error_log(json_encode([
		"payload" => $JSONPayload,
		"signature" => $signature,
		"authHeader" => $authHeader,
		"url" => $url,
		"nonce" => $nonce
	]));

	// Initialize cURL session
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		"Authorization: $authHeader",
		'Content-Type: application/json'
	]);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $JSONPayload);

	// Execute cURL request
	$response = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	error_log(json_encode($response));

	if (curl_errno($ch)) {
		$errorMsg = curl_error($ch);
		curl_close($ch);
		echo "Error: " . $errorMsg;
	} else {
		curl_close($ch);
		$response = json_decode($response, true); // Decode JSON response

		if ($httpCode !== 200) {
			if (isset($response['error'])) {
				echo "Error: " . $response['error']['message'];
			} else {
				echo "Error: Unexpected response " . json_encode($response);
			}
		} else {
			if (isset($response["success"]) && $response["success"] == true) {
				print_r($response['payload']);
			} else {
				print_r($response);
			}
		}
	}
});



Route::get('g', function () {
	// $order = Order::with('customer', 'seller', 'product')->first();
	// return response()->json($order);
	$groupBy = request()->get('filter');
	$year = [
		'chart_title' => 'Users by year',
		'report_type' => 'group_by_date', // group_by_string or group_by_date
		'model' => User::class,
		'group_by_field' => 'created_at',
		'group_by_period' => $groupBy ?? 'year', // day/week/month/year
		'chart_type' => 'line',
	];
	$month = [
		'chart_title' => 'Users by month',
		'report_type' => 'group_by_date',
		'model' => User::class,
		'group_by_field' => 'created_at',
		'group_by_period' => $groupBy ?? 'month', // day/week/month/year
		'chart_type' => 'pie',
	];
	$week = [
		'chart_title' => 'Users by week',
		'report_type' => 'group_by_date',
		'model' => User::class,
		'group_by_field' => 'created_at',
		'group_by_period' => $groupBy ?? 'week', // day/week/month/year
		'chart_type' => 'bar',
	];
	$day = [
		'chart_title' => 'Users by day',
		'report_type' => 'group_by_date',
		'model' => User::class,
		'group_by_field' => 'created_at',
		'group_by_period' => $groupBy ?? 'day', // day/week/month/year
		'chart_type' => 'bar',
	];
	$chart1 = new LaravelChart($day);
	$chart2 = new LaravelChart($week);
	$chart3 = new LaravelChart($month);
	$chart4 = new LaravelChart($year);
	return [
		'day' => $chart1->getDatasets(),
		'week' => $chart2->getDatasets(),
		'month' => $chart3->getDatasets(),
		'year' => $chart4->getDatasets(),
	];
});


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
