<?php

namespace Modules\Suppliers\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\KwikDeliveryController;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class SuppliersController extends Controller
{
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

            $order = Order::with('product', 'seller', 'user')->where('seller_id', auth()->id())->where("order_id", $request->order_id)->first();
            if (empty($order)) {
                return get_error_response("Order not found!", ['error' => "Selected order not found"], 404);
            }
            
            $order->status = $request->status;
            
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
                }
                return get_success_response("Order status updated successfully", $order, 200);
            }
        } catch (\Throwable $th) {
            return get_error_response("Order update failed!", ['error' => $th->getMessage()] , 500);
        }
    }
}
