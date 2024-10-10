<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Notifications\Order\OrderPlacedSuccessfully;
use App\Services\KwikDeliveryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderController extends Controller
{
    public function index()
    {
        return view('order');
    }

    public function place_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "delivery_type" => "required|in:home_delivery,pick_up",
            "delivery_address" => "required_if:delivery_type,home_delivery|string",
            "quantity" => "required|integer|min:1",
            "additional_info" => "sometimes|string",
            "product_id" => "required|exists:products,id",
            "delivery_longitude" => "required_if:delivery_type,home_delivery|string",
            "delivery_latitude" => "required_if:delivery_type,home_delivery|string",
        ]);

        if ($validator->fails()) {
            return get_error_response("Validation error", $validator->errors(), 422);
        }

        try {
            $product = Product::findOrFail($request->product_id);
            $user = $request->user();
            $wallet = $user->getWallet("ngn");

            if (!$product) {
                return get_error_response("Product not found!", ["error" => "Product not found!"], 404);
            }

            if (!$wallet) {
                return get_error_response("User wallet not found", ["error" => "User wallet not found"], 404);
            }

            $orderData = $validator->validated();
            $orderData["user_id"] = $user->id;
            $orderData["seller_id"] = $product->seller_id;
            $orderData["status"] = "pending";
            $orderData["total_price"] = ($product->price * $request->quantity);

            $wallet->withdraw($orderData["total_price"] * 100, ["description" => "Order placed"]);

            $order = Order::create($orderData);

            if($order) {
                $kwik = new KwikDeliveryController();
                $kwikOrder = $kwik->createPickupAndDeliveryTask($order->id);
                
                $order->order_id = $kwikOrder['unique_order_id'] ?? "error_please_contact_spport";
                $order->save();
            }
            
            $user->notify(new OrderPlacedSuccessfully($order));
            return get_success_response($order, "Order placed successfully", 201);
        } catch (ModelNotFoundException $e) {
            return get_error_response("Product not found", [], 404);
        } catch (\Exception $e) {
            return get_error_response("Order placement failed", ["error" => $e->getMessage()], 500);
        }
    }

    public function getUserOrders()
    {
        try {
            $orders = Order::with('product', 'seller', 'user')->where('user_id', auth()->id())->get();
            return get_success_response($orders, "User orders retrieved successfully");
        } catch (\Exception $e) {
            return get_error_response("Failed to retrieve user orders", ["error" => $e->getMessage()], 500);
        }
    }

    public function getOrder($orderId)
    {
        try {
            $orders = Order::with('product', 'seller', 'user')
                ->where(function($query) {
                    $query->where('user_id', auth()->id())
                          ->orWhere('seller_id', auth()->id());
                })
                ->findOrFail($orderId);
            return get_success_response($orders, "Order retrieved successfully");
        } catch (ModelNotFoundException $e) {
            return get_error_response("Order not found", [], 404);
        } catch (\Exception $e) {
            return get_error_response("Failed to retrieve order", ["error" => $e->getMessage()], 500);
        }
    }

    public function getOrderStatus($id)
    {
        try {
            $order = Order::where('user_id', auth()->id())->findOrFail($id);
            return get_success_response($order, "Order status retrieved successfully");
        } catch (ModelNotFoundException $e) {
            return get_error_response("Order not found", [], 404);
        } catch (\Exception $e) {
            return get_error_response("Failed to retrieve order status", ["error" => $e->getMessage()], 500);
        }
    }

    public function getOrdersByStatus($status)
    {
        try {
            $orders = Order::where('user_id', auth()->id())->where('status', $status)->get();
            return get_success_response($orders, "Orders retrieved successfully");
        } catch (\Exception $e) {
            return get_error_response("Failed to retrieve orders", ["error" => $e->getMessage()], 500);
        }
    }

    public function getSortedOrders()
    {
        try {
            $orders = Order::where('user_id', auth()->id())->orderBy('status')->get();
            return get_success_response($orders, "Sorted orders retrieved successfully");
        } catch (\Exception $e) {
            return get_error_response("Failed to retrieve sorted orders", ["error" => $e->getMessage()], 500);
        }
    }

    public function trackOrder(Request $request)
    {
        try {
            $order = Order::where('user_id', auth()->id())->findOrFail($request->orderId);
            $kwik = new KwikDeliveryService();
            $trackingDetails = $kwik->getJobDetails($order->kwik_order_id);
            return get_success_response($trackingDetails, "Order tracking details retrieved successfully");
        } catch (ModelNotFoundException $e) {
            return get_error_response("Order not found", [], 404);
        } catch (\Exception $e) {
            return get_error_response("Failed to track order", ["error" => $e->getMessage()], 400);
        }
    }
}
