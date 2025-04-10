<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderModel;
use App\Models\Product;
use App\Notifications\CustomNotification;
use App\Notifications\Order\OrderPlacedSuccessfully;
use App\Services\KwikDeliveryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Log;


class OrderController extends Controller
{
    public function index()
    {
        return view('order');
    }
    
    public function place_order(Request $request)
    {
        try {
            $product = Product::findOrFail($request->product_id);

            // Validation rules
            $validationRules = [
                "delivery_type" => "required|in:home_delivery,pick_up",
                "delivery_address" => "required_if:delivery_type,home_delivery|string",
                "quantity" => "required|integer|min:1",
                "product_id" => "required|exists:products,id",
                "delivery_longitude" => "required_if:delivery_type,home_delivery",
                "delivery_latitude" => "required_if:delivery_type,home_delivery",
                "duration_type" => "sometimes",
                "lease_duration" => "sometimes",
                "lease_rate" => "sometimes",
                "lease_notes" => "sometimes",
            ];

            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                Log::error('Order placement validation error: ', ['error'=> $validator->errors()->toArray()]);
                return get_error_response("Validation error", $validator->errors(), 422);
            }

            $user = $request->user();
            $wallet = $user->getWallet("ngn");

            if (!$wallet) {
                return get_error_response("User wallet not found", ["error" => "User wallet not found"], 404);
            }

            $orderData = $validator->validated();
            $orderData["user_id"] = $user->id;
            $orderData["seller_id"] = $product->seller_id;
            $orderData["status"] = "pending";

            // Default shipping fee
            $shippingFee = 0;

            if ($request->delivery_type === 'home_delivery') {
                // Calculate total price and shipping fee
                $kwik = new KwikDeliveryController();
                $shippingFee = $kwik->calculatePricing(
                    $orderData['delivery_address'],
                    $orderData['delivery_latitude'],
                    $orderData['delivery_longitude'],
                    $product,
                    $product->seller,
                    $user
                );

                if (is_array($shippingFee) || isset($shippingFee['error'])) {
                    return get_error_response($shippingFee['error'], ["error" => $shippingFee['error']], 400);
                }
            }

            $orderData["shipping_fee"] = $shippingFee;
            $orderData["product_category_id"] = $product->category_id;
            $orderData["product_service_category_id"] = $product->category_id;

            // Calculate total price for Rentable and Non-Rentable Products
            if ($request->has('lease_duration')) {
                // Rentable Product
                $rentingRate = $request->lease_rate;
                $itemPrice = $request->quantity * $rentingRate;
                $vatAmount = 0.075 * $itemPrice;
                $totalPrice = $shippingFee + $itemPrice + $vatAmount;
                $orderData['rentable_price'] = $itemPrice;
            } else {
                // Non-Rentable Product
                $productPrice = $product->price;
                $itemPrice = $request->quantity * $productPrice;
                $vatAmount = 0.075 * $itemPrice;
                $totalPrice = $shippingFee + $itemPrice + $vatAmount;
            }

            $orderData["total_price"] = round($totalPrice, 2);
            \Log::info('Amount due now is ', ['amount_due' => $orderData]);
            // Withdraw from wallet
            if(!$wallet->withdrawal(round($orderData["total_price"] * 100, 2), ["description" => "Order placed", "Order placement"])){
                return ['error' => 'Insufficient Balance'];
            }

            // Create order
            $order = Order::create($orderData);

            // Notify the user
            if ($order) {
                // notify seller
                $seller = User::whereId($product->seller_id)->first();
                $seller->notify(new CustomNotification('New order from a customer', "New order from a customer"));
                return get_success_response($order, "Order placed successfully", 201);
            }

        } catch (ModelNotFoundException $e) {
            return get_error_response("Product not found", ['error' => "Product not found"], 404);
        } catch (\Exception $e) {
            // Log the actual error for debugging
            Log::error('Order placement failed error: ', ['error'=> $e->getMessage(), 'trace' => $e->getTrace()]);
            return get_error_response("Order placement failed", ["error" => $e->getMessage()], 500);
        }
    }

    public function getUserOrders()
    {
        try {
            $orders = Order::with('product', 'seller', 'user');
            if(request()->user()->current_role == 'suppliers'){
                $orders = $orders->where('_account_type', active_role())->latest()->where('seller_id', auth()->id())->limit(100)->get();
            } else {
                $orders = $orders->where('_account_type', active_role())->latest()->where('user_id', auth()->id())->limit(100)->get();
            }
            return get_success_response($orders, "User orders retrieved successfully");
        } catch (\Exception $e) {
            \Log::error('Error retrieving user orders: ' . $e->getMessage());
            return get_error_response("Failed to retrieve user orders", ["error" => $e->getMessage()], 500);
        }
    }
    
    public function getOrder($orderId)
    {
        try {
            $orders = OrderModel::with('product', 'seller', 'user')->where('_account_type', active_role())->findOrFail($orderId);
            return get_success_response($orders, "Order retrieved successfully");
        } catch (ModelNotFoundException $e) {
            return get_error_response("Order not found", ['error' => "Order not found"], 404);
        } catch (\Exception $e) {
            \Log::error('Error retrieving order: ' . $e->getMessage());
            return get_error_response("Failed to retrieve order", ["error" => $e->getMessage()], 500);
        }
    }

    public function getOrderStatus($id)
    {
        try {
            $order = OrderModel::where('user_id', auth()->id())->findOrFail($id);
            return get_success_response($order, "Order status retrieved successfully");
        } catch (ModelNotFoundException $e) {
            return get_error_response("Order not found", [], 404);
        } catch (\Exception $e) {
            \Log::error('Error retrieving order status: ' . $e->getMessage());
            return get_error_response("Failed to retrieve order status", ["error" => $e->getMessage()], 500);
        }
    }

    public function getOrdersByStatus($status)
    {
        try {
            $orders = OrderModel::where('user_id', auth()->id())->where('_account_type', active_role())->where('status', $status)->get();
            return get_success_response($orders, "Orders retrieved successfully");
        } catch (\Exception $e) {
            \Log::error('Error retrieving orders by status: ' . $e->getMessage());
            return get_error_response("Failed to retrieve orders", ["error" => $e->getMessage()], 500);
        }
    }

    public function getSortedOrders()
    {
        try {
            $orders = OrderModel::where('user_id', auth()->id())->where('_account_type', active_role())->orderBy('status')->latest()->get();
            return get_success_response($orders, "Sorted orders retrieved successfully");
        } catch (\Exception $e) {
            \Log::error('Error retrieving sorted orders: ' . $e->getMessage());
            return get_error_response("Failed to retrieve sorted orders", ["error" => $e->getMessage()], 500);
        }
    }

    public function trackOrder(Request $request)
    {
        try {
            $order = OrderModel::where('user_id', auth()->id())->findOrFail($request->orderId);
            $kwik = new KwikDeliveryService();
            $trackingDetails = $kwik->getJobDetails($order->kwik_order_id);
            return get_success_response($trackingDetails, "Order tracking details retrieved successfully");
        } catch (ModelNotFoundException $e) {
            return get_error_response("Order not found", ['error' => "Order not found"], 404);
        } catch (\Exception $e) {
            \Log::error('Error tracking order: ' . $e->getMessage());
            return get_error_response("Failed to track order", ["error" => $e->getMessage()], 400);
        }
    }

    public function getRentablePrice($productId, $durationKey)
    {
        $product = Product::findOrFail($productId);
        $rentablePrice = $product->rentable_price;
        if (isset($rentablePrice) && is_array($rentablePrice)) {
            return $rentablePrice[$durationKey] ?? null;
        }

        return null;
    }

    public function cancelOrder($orderId)
    {
        try {
            $order = OrderModel::whereId($orderId)->first();

            if (!$order) {
                return get_error_response("Order not found", [], 404);
            }

            $orderStatus = [
                'UPCOMING', 'STARTED', 'ENDED', 'FAILED', 'ARRIVED',
                'UNASSIGNED', 'ACCEPTED', 'DECLINE', 'CANCEL', 'DELETED'
            ];

            // Ensure the status is compared as a string
            $currentStatus = strtoupper($order->status);

            if (in_array($currentStatus, $orderStatus)) {
                return get_error_response("Order cannot be canceled.", [], 400);
            }

            $order->status = "CANCEL";

            if ($order->save()) {
                $user = auth()->user();
                // process customer refund 
                $wallet = $user->getWallet("ngn");

                if (!$wallet) {
                    return get_error_response("User wallet not found", ["error" => "User wallet not found"], 404);
                }

                if(!$wallet->deposit($order->total_price * 100, ["description" => "Order refund for ORDER ID: {$order->id}", "Order placement refunded"])){
                    return ['error' => 'Insufficient Balance'];
                }
                
                $seller = User::find($order->seller_id);
                $seller->notify(new CustomNotification('Order canceled by customer', 'Order canceled by customer'));
                return get_success_response($order, "Order canceled successfully", 200);
            }

            return get_error_response("Failed to cancel order. Please try again.", [], 500);

        } catch (\Exception $e) {
            return get_error_response("An unexpected error occurred.", ['error' => $e->getMessage()], 500);
        }
    }
    
    public function getShippingRate()
    {
        $orderData = request();
        $product = Product::findOrFail($orderData->product_id);
        $user = $orderData->user();
        $orderData["user_id"] = $user->id;
        $orderData["seller_id"] = $product->seller_id;

        // Calculate total price and shipping fee
        $kwik = new KwikDeliveryController();
        $shippingFee = $kwik->calculatePricing(
            $orderData['delivery_address'],
            $orderData['delivery_latitude'],
            $orderData['delivery_longitude'],
            $product,
            $product->seller,
            $user, true
        );
        $orderData["shipping_fee"] = $shippingFee; 

        if(isset($shippingFee['error'])) {
            return get_error_response( $shippingFee['error'], ["error" => $shippingFee['error']], 400);
        }
        return get_success_response($shippingFee, "Shipping rate retrieved successfully", 200);
    }
}
