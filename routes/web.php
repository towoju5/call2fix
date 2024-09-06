<?php

use App\Events\MessageSent;
use App\Http\Controllers\Admin\ServiceAreaController;
use App\Http\Middleware\JsonRequestMiddleware;
use App\Models\Category;
use App\Models\ChatMessage;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;


Route::middleware('web')->domain(env('APP_URL'))->group(function () {
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


Route::get('p', function () {
    Role::truncate();
    $role = [
        Role::create(['guard_name' => 'web', 'name' => 'artisan']),
        Role::create(['guard_name' => 'web', 'name' => 'providers']),
        Role::create(['guard_name' => 'web', 'name' => 'co-operate_accounts']),
        Role::create(['guard_name' => 'web', 'name' => 'private_accounts']),
        Role::create(['guard_name' => 'web', 'name' => 'affiliates']),
        Role::create(['guard_name' => 'web', 'name' => 'suppliers']),
    ];

    return response()->json($role);
});

// Route::prefix('admin')->name('admin.')->group(function () {
//     Route::resource('service_areas', ServiceAreaController::class);
// });


Route::get('/chat/{friend}', function (User $friend) {
    return view('chat', [
        'friend' => $friend
    ]);
})->middleware(['auth'])->name('chat');


Route::post('/messages/{friend}', function (User $friend) {
    $message = ChatMessage::create([
        'sender_id' => auth()->id(),
        'receiver_id' => $friend->id,
        'text' => request()->input('message')
    ]);

    broadcast(new MessageSent($message));

    return $message;
});

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
})->middleware(['auth']);