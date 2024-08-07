<?php

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // $users = User::all();
    // return get_success_response($users, "User retrieved successfully");
    return view('welcome');
});


Route::prefix('api/v1')->withoutMiddleware([VerifyCsrfToken::class])->group(function () {
    require_once('api.php');
});