<?php

use App\Http\Middleware\JsonRequestMiddleware;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Category::all();
    // $users = User::all();
    // return get_success_response($users, "User retrieved successfully");
    return view('welcome');
});


// Route::prefix('api/v1')->withoutMiddleware([VerifyCsrfToken::class])->group(function () {
//     require_once('api.php');
// });  


Route::fallback(function (){
    return get_error_response("API resource not found", [
        "error" => "API resource not found"
    ], 404);
});