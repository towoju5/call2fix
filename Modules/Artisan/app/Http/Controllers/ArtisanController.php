<?php

namespace Modules\Artisan\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ItemRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ArtisanController extends Controller
{
    public function index()
    {
        try {
            $orders = Order::whereArtisanId(auth()->id())->latest()->get();
            return get_success_response($orders, "All tasks retrieved successfully");
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()]);
        }
    }

    public function requests()
    {
        try {
            $reqs = ItemRequest::whereArtisanId(auth()->id())->latest()->get();
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()]);
        }
    }

    // public function addNewRequest(Request $request)
}
