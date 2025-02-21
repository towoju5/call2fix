<?php

namespace Modules\Suppliers\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\KwikDeliveryController;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator, DB;
use App\Models\OrderModel;

class SuppliersController extends Controller
{
    public function orders()
    {
        try {
            $orders = DB::table('orders')
                ->join('products', 'orders.product_id', '=', 'products.id')
                ->join('users as sellers', 'orders.seller_id', '=', 'sellers.id') // Seller information
                ->join('users as users', 'orders.user_id', '=', 'users.id') // User information
                ->where('sellers.id', auth()->id()) // Authenticated seller
                ->select(
                    'orders.*',
                    DB::raw('JSON_OBJECT(
                        "id", products.id,
                        "name", products.name,
                        "description", products.description,
                        "price", products.price,
                        "category_id", products.category_id,
                        "stock", products.stock,
                        "sku", products.sku,
                        "product_currency", products.product_currency,
                        "product_location", products.product_location,
                        "product_image", products.product_image,
                        "weight", products.weight,
                        "dimensions", products.dimensions,
                        "is_active", products.is_active,
                        "is_leasable", products.is_leasable,
                        "rentable_price", products.rentable_price,
                        "product_longitude", products.product_longitude,
                        "product_latitude", products.product_latitude
                    ) as product'),
                    DB::raw('JSON_OBJECT(
                        "id", sellers.id,
                        "first_name", sellers.first_name,
                        "last_name", sellers.last_name,
                        "username", sellers.username,
                        "email", sellers.email,
                        "phone", sellers.phone,
                        "profile_picture", sellers.profile_picture,
                        "account_type", sellers.account_type
                    ) as seller'),
                    DB::raw('JSON_OBJECT(
                        "id", users.id,
                        "first_name", users.first_name,
                        "last_name", users.last_name,
                        "username", users.username,
                        "email", users.email,
                        "phone", users.phone,
                        "profile_picture", users.profile_picture,
                        "account_type", users.account_type
                    ) as user')
                )
                ->latest()
                ->limit(100) // Keeping a reasonable limit to prevent excessive data load
                ->get(); // Fetching all records without pagination
        
            // Transform collection to parse JSON fields
            $orders->transform(function ($order) {
                $order->product = json_decode($order->product);
                $order->seller = json_decode($order->seller);
                $order->user = json_decode($order->user);
                return $order;
            });
        
            return get_success_response($orders, "Orders retrieved successfully", 200);
        } catch (\Throwable $th) {
            return get_error_response("Order retrieval failed!", ['error' => $th->getMessage()], 400);
        }
        
    }
    
    
    public function updateOrder(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'order_id' => 'required|string',
                'status' => 'required|in:accept,reject',
            ]);

            if ($validate->fails()) {
                return get_error_response("validation errors", $validate->errors(), 422);
            }

            $order = OrderModel::with('product', 'seller', 'user')
                        // ->where('seller_id', auth()->id())
                        ->whereId($request->order_id)->first();
            
            if (empty($order)) {
                return get_error_response("Order not found!", ['error' => "Selected order not found"], 404);
            }
            
            if(strtolower($order->status) === 'reject') {
                return get_error_response("Order already canceled!", ['error' => "Order already canceled"], 403);
            }
            
            if($request->status == "accept") {
                $status = "ACCEPTED";
            }
            
            $order->status = $status;
            
            if ($order->save()) {
                if(strtolower($request->status) === 'accept' && $order->product->delivery_type === 'home_delivery') {
                    // create delivery request on behalf of the seller on Kwik Delivery
                    $kwik = new KwikDeliveryController();
                    $place_order = $kwik->createPickupAndDeliveryTask(
                        $order->delivery_address, 
                        $order->delivery_latitude, 
                        $order->delivery_longitude, 
                        $order->product, 
                        $order->seller, 
                        $order->user);
                \Log::info("Kwik Order Placement response", $place_order);
                
                } else if(strtolower($request->status) === 'reject') {
                    // refund customer and cancel order
                }
                
                return get_success_response($order, "Order status updated successfully", 200);
            }
        } catch (\Throwable $th) {
            return get_error_response("Order update failed!", ['error' => $th->getMessage()] , 400);
        }
    }
}
