<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index()
    {
        return view('order');
    }

    public function place_order(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "delivery_type" => "required|in:home_delivery,pick_up",
                "delivery_address" => "required_if:delivery_type,home_delivery|string",
                "quantity" => "required|integer|min:1",
                "additional_info" => "sometimes|string",
                "product_id" => "required|exists:products,id",
                // "debit_wallet"
            ]);

            if ($validator->fails()) {
                return get_error_response("Validation error", $validator->errors(), 422);
            }

            $product = Product::find($request->product_id);

            $user = $request->user();

            $orderData = $validator->validated();
            $orderData["user_id"] = $user->id;
            $orderData["seller_id"] = $product->seller_id;
            $orderData["status"] = "pending";
            $orderData["total_price"] = ($product->price * $request->quantity);

            // retrieve user wallet & balance
            $wallet = $user->getWallet("ngn");
            $wallet->deposit(40000*100);
            if(!$wallet) {
                return get_error_response("User wallet not found", [], 404);
            }

            $wallet->withdraw($orderData["total_price"] * 100, ["description" => "Order placed"]);

            $order = Order::create($orderData);

            if ($order) {
                return get_success_response($order, "Order placed successfully", 201);
            }

            return get_error_response("Order placement failed!", ["error" => "Order placement failed"] , 500);
        } catch (\Exception $e) {
            return get_error_response($e->getMessage(), ["error" => $e->getMessage()], 500);
        }
    }

    public function getUserOrders()
    {
        try {
            $orders = Order::with('product', 'seller', 'user')->where('user_id', auth()->id())->get();
            return get_success_response($orders, "User orders retrieved successfully", 200);
        } catch (\Exception $e) {
            return get_error_response("Failed to retrieve user orders", ["message" => $e->getMessage()], 500);
        }
    }

    public function getOrder($orderId)
    {
        try {
            $orders = Order::with('product', 'seller', 'user')->whereUserId(auth()->id())->orWhere('seller_id', auth()->id())->get();
            return get_success_response($orders, "User orders retrieved successfully", 200);
        } catch (\Exception $e) {
            return get_error_response("Failed to retrieve user orders", ["message" => $e->getMessage()], 500);
        }
    }

    public function getOrderStatus($id)
    {
        try {
            $order = Order::whereUserId(auth()->id())->whereId($id)->firstOrFail();
            return get_success_response($order, "Order status retrieved successfully");
        } catch (\Exception $e) {
            return get_error_response("Failed to retrieve order status", [], 404);
        }
    }

    public function getOrdersByStatus($status)
    {
        try {
            $orders = Order::whereUserId(auth()->id())->where('status', $status)->get();
            return get_success_response($orders, "Orders retrieved successfully");
        } catch (\Exception $e) {
            return get_error_response("Failed to retrieve orders", [], 500);
        }
    }

    public function getSortedOrders()
    {
        try {
            $orders = Order::whereUserId(auth()->id())->orderBy('status')->get();
            return get_success_response($orders, "Sorted orders retrieved successfully");
        } catch (\Exception $e) {
            return get_error_response("Failed to retrieve sorted orders", [], 500);
        }
    }

    // public function
}
