<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderModel;
use App\Models\Product;
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
                "delivery_longitude" => "required_if:delivery_type,home_delivery|string",
                "delivery_latitude" => "required_if:delivery_type,home_delivery|string",
                "duration_type" => "required|in:days,weekly,months",
                "lease_duration" => "sometimes|integer|min:1",
                "lease_rate" => "sometimes|numeric|min:0",
                "lease_notes" => "sometimes|string",
            ];
    
            $validator = Validator::make($request->all(), $validationRules);
    
            if ($validator->fails()) {
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
            } else {
                $shippingFee = 0;
            }
    
            $orderData["shipping_fee"] = $shippingFee; 
            $orderData["product_category_id"] = $product->category_id; 
            $orderData["product_service_category_id"] = $product->category_id; 
    
            if (is_array($shippingFee) || isset($shippingFee['error'])) {
                return get_error_response($shippingFee['error'], ["error" => $shippingFee['error']], 400);
            }
    
            // Calculate total price based on lease or normal purchase
            if ($request->has('lease_duration')) {
                $rentablePrice = $this->getRentablePrice($product->id, $orderData['duration_type']);
                $orderData['rentable_price'] = $rentablePrice * $request->lease_duration;
                $subtotal = ($rentablePrice * $orderData['quantity']) + $shippingFee;
            } else {
                $subtotal = ($product->price * $orderData['quantity']) + $shippingFee;
            }
    
            // Apply VAT for both rentable and non-rentable products
            $vatAmount = ($subtotal * get_settings_value('vat_percentage', 7.5)) / 100;
            $orderData["total_price"] = floatval($subtotal + $vatAmount);
    
            // Withdraw from wallet
            $wallet->withdrawal($orderData["total_price"], ["description" => "Order placed", "Order placement"]);
    
            // Create order
            $order = Order::create($orderData);
    
            // Notify the user
            if ($order) {
                // $user->notify(new OrderPlacedSuccessfully($order));
                return get_success_response($order, "Order placed successfully", 201);
            }
    
        } catch (ModelNotFoundException $e) {
            return get_error_response("Product not found", [], 404);
        } catch (\Exception $e) {
            // Log the actual error for debugging
            return get_error_response("Order placement failed", ["error" => $e->getMessage()], 500);
        }
    }
    
    

    public function getUserOrders()
    {
        try {
            $orders = Order::with('product', 'seller', 'user');
            if(request()->user()->current_role == 'suppliers'){
                $orders = $orders->latest()->where('seller_id', auth()->id())->paginate(get_settings_value('per_page') ?? 10);
            } else {
                $orders = $orders->latest()->where('user_id', auth()->id())->paginate(get_settings_value('per_page') ?? 10);
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
            $orders = OrderModel::with('product', 'seller', 'user')
                ->where(function ($query) {
                    $query->where('user_id', auth()->id())
                        ->orWhere('seller_id', auth()->id());
                })
                ->findOrFail($orderId);

            return get_success_response($orders, "Order retrieved successfully");
        } catch (ModelNotFoundException $e) {
            return get_error_response("Order not found", [], 404);
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
            $orders = OrderModel::where('user_id', auth()->id())->where('status', $status)->get();
            return get_success_response($orders, "Orders retrieved successfully");
        } catch (\Exception $e) {
            \Log::error('Error retrieving orders by status: ' . $e->getMessage());
            return get_error_response("Failed to retrieve orders", ["error" => $e->getMessage()], 500);
        }
    }

    public function getSortedOrders()
    {
        try {
            $orders = OrderModel::where('user_id', auth()->id())->orderBy('status')->latest()->get();
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
            return get_error_response("Order not found", [], 404);
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
            $order = OrderModel::whereId($orderId)->whereUserId(auth()->id())->first();
            $orderStatus = ['STARTED', 'ENDED', 'FAILED', 'ARRIVED', 'UNASSIGNED', 'ACCEPTED', 'DECLINE', 'CANCEL', 'Deleted'];
            if ($order && in_array($order->status, $orderStatus)) {
                $order->status = "CANCEL";
                if ($order->save()) {
                    return get_success_response($order, "Order canceled successfully", 200);
                }
    
                return get_error_response("Failed to cancel order. Please try again.", [], 500);
            }
    
            return get_error_response("Order cannot be canceled or not found.", [], 400);
        } catch (ModelNotFoundException $e) {
            return get_error_response("Order not found", [], 404);
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
