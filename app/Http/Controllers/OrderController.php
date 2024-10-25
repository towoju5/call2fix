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
        try {
            $product = Product::findOrFail($request->product_id);

            // Validation rules
            $validationRules = [
                "delivery_type" => "required|in:home_delivery,pick_up",
                "delivery_address" => "required_if:delivery_type,home_delivery|string",
                "quantity" => "required|integer|min:1",
                "additional_info" => "sometimes|string",
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
            $orderData["shipping_fee"] = $shippingFee;

            if (is_array($shippingFee) || isset($shippingFee['error'])) {
                return get_error_response($shippingFee['error'], ["error" => $shippingFee['error']], 400);
            }

            // Calculate total price based on lease or normal purchase
            if ($request->has('lease_duration')) {
                $rentablePrice = $this->getRentablePrice($product->id, $orderData['duration_type']);
                $orderData['rentable_price'] = $rentablePrice;
                $orderData["total_price"] = floatval($rentablePrice * $orderData['quantity']) + $shippingFee;
            } else {
                $orderData["total_price"] = floatval($product->price * $orderData['quantity']) + $shippingFee;
            }

            // Withdraw from wallet
            $wallet->withdraw($orderData["total_price"], get_default_currency($user->id), ["description" => "Order placed", "Order placement"]);

            // Create order
            $order = Order::create($orderData);

            // Notify the user
            if ($order) {
                $user->notify(new OrderPlacedSuccessfully($order));

                return get_success_response($order, "Order placed successfully", 201);
            }

        } catch (ModelNotFoundException $e) {
            return get_error_response("Product not found", [], 404);
        } catch (\Exception $e) {
            // Log the actual error for debugging
            \Log::error('Order placement error: ' . $e->getMessage());
            return get_error_response("Order placement failed", ["error" => $e->getMessage()], 500);
        }
    }


    public function getUserOrders()
    {
        try {
            $orders = Order::with('product', 'seller', 'user')->where('user_id', auth()->id())->get();
            return get_success_response($orders, "User orders retrieved successfully");
        } catch (\Exception $e) {
            \Log::error('Error retrieving user orders: ' . $e->getMessage());
            return get_error_response("Failed to retrieve user orders", ["error" => $e->getMessage()], 500);
        }
    }

    public function getOrder($orderId)
    {
        try {
            $orders = Order::with('product', 'seller', 'user')
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
            $order = Order::where('user_id', auth()->id())->findOrFail($id);
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
            $orders = Order::where('user_id', auth()->id())->where('status', $status)->get();
            return get_success_response($orders, "Orders retrieved successfully");
        } catch (\Exception $e) {
            \Log::error('Error retrieving orders by status: ' . $e->getMessage());
            return get_error_response("Failed to retrieve orders", ["error" => $e->getMessage()], 500);
        }
    }

    public function getSortedOrders()
    {
        try {
            $orders = Order::where('user_id', auth()->id())->orderBy('status')->get();
            return get_success_response($orders, "Sorted orders retrieved successfully");
        } catch (\Exception $e) {
            \Log::error('Error retrieving sorted orders: ' . $e->getMessage());
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

    public function acceptOrder($id)
    {
        try {
            $order = Order::where('user_id', auth()->id())->findOrFail($id);
            $order->status = 'accepted';
            $order->save();

            return get_success_response($order, "Order accepted successfully");
        } catch (ModelNotFoundException $e) {
            return get_error_response("Order not found", [], 404);
        } catch (\Exception $e) {
            \Log::error('Error accepting order: ' . $e->getMessage());
            return get_error_response("Failed to accept order", ["error" => $e->getMessage()], 500);
        }
    }
    
    public function rejectOrder($id)
    {
        try {
            $order = Order::where('user_id', auth()->id())->findOrFail($id);
            $order->status = 'rejected';
            $order->save();

            return get_success_response($order, "Order rejected successfully");
        } catch (ModelNotFoundException $e) {
            return get_error_response("Order not found", [], 404);
        } catch (\Exception $e) {
            \Log::error('Error rejecting order: ' . $e->getMessage());
            return get_error_response("Failed to reject order", ["error" => $e->getMessage()], 500);
        }
    }

}
