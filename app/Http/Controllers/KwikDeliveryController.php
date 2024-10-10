<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\KwikDeliveryService;
use Illuminate\Http\Request;
use Log;

class KwikDeliveryController extends Controller
{
    protected $kwikService;

    public function __construct()
    {
        $kwikService = new KwikDeliveryService();
        $this->kwikService = $kwikService;
    }

    public function createPickupAndDeliveryTask($orderId)
    {
        $order = Order::with('user', 'seller', 'product')->whereId($orderId)->first();
        $task = [
            'auto_assignment' => true,
            'pickup_custom_field_template' => 'default_pickup_template',
            'is_schedule_task' => 0,
            'pickups' => [
                [
                    'address' => $order->product->product_location,
                    'email' => $order->seller->email,
                    'phone' => $order->seller->phone,
                    'latitude' => $order->product->latitude ?? '40.7128',
                    'longitude' => $order->product->longitude ?? '-74.0060',
                ]
            ],
            'deliveries' => [
                [
                    'address' => $order->delivery_address,
                    'email' => $order->user->email,
                    'phone' => $order->user->phone,
                    'latitude' => $order->delivery_latitude,
                    'longitude' => $order->delivery_longitude,
                    'has_return_task' => false,
                ]
            ],
            'is_loader_required' => 0,
            'vehicle_id' => $this->fetchVehicleId(),
            'is_cod_job' => 0,
        ];

        $result = $this->kwikService->createPickupAndDeliveryTask($task);

        Log::error("Kwik Delivery response: ", ["response" => $result, "payload" => $task]);

        return $result;
    }

    public function createReturnTask($orderId)
    {
        $order = Order::with('customer', 'seller')->whereId($orderId)->first();
        $task = [
            'auto_assignment' => true,
            'pickup_custom_field_template' => 'default_pickup_template',
            'is_schedule_task' => 0,
            'pickups' => [
                [
                    'address' => '123 Pickup St, City',
                    'email' => 'pickup@example.com',
                    'phone' => '1234567890',
                    'latitude' => '40.7128',
                    'longitude' => '-74.0060',
                ]
            ],
            'deliveries' => [
                [
                    'address' => '456 Delivery Ave, Town',
                    'email' => 'delivery@example.com',
                    'phone' => '0987654321',
                    'latitude' => '40.7306',
                    'longitude' => '-73.9352',
                    'has_return_task' => true,
                ]
            ],
            'is_loader_required' => 0,
            'vehicle_id' => 1,
            'is_cod_job' => 0,
        ];

        return $this->kwikService->createReturnTask($task);
    }

    public function cancelTask(Request $request)
    {
        $task = [
            'job_id' => '12345',
            'job_status' => 9,
        ];

        return $this->kwikService->cancelTask($task);
    }

    public function fetchVehicleId()
    {
        if($weight < 100) {
            $size = 1;
        }
        $task = [
            'is_vendor' => 1,
            'size' => 1, // 0 for bike, 1 for small, 2 for medium, 3 for large
        ];

        return $this->kwikService->fetchVehicleId($task);
    }

    public function calculatePricing($orderId)
    {
        $order = Order::with('user', 'seller', 'product')->whereId($orderId)->first();
        $task = [
            'custom_field_template' => 'default_template',
            'auto_assignment' => true,
            'pickup_custom_field_template' => 'default_pickup_template',
            'user_id' => 1,
            'payment_method' => 32,
            'form_id' => 2,
            'is_schedule_task' => 0,
            'pickups' => [
                [
                    'address' => $order->product->product_location,
                    'email' => $order->seller->email,
                    'phone' => $order->seller->phone,
                    'latitude' => $order->product->latitude ?? '40.7128',
                    'longitude' => $order->product->longitude ?? '-74.0060',
                ]
            ],
            'deliveries' => [
                [
                    'address' => $order->delivery_address,
                    'email' => $order->user->email,
                    'phone' => $order->user->phone,
                    'latitude' => $order->delivery_latitude,
                    'longitude' => $order->delivery_longitude,
                    'has_return_task' => false,
                ]
            ],
            'is_loader_required' => 0,
            'vehicle_id' => 1,
            'is_cod_job' => 0,
        ];

        return $this->kwikService->calculatePricing($task);
    }

    public function getEstimatedFare($orderId)
    {
        $order = Order::with('user', 'seller')->whereId($orderId)->first();
        $task = [
            'benefit_type' => 1,
            'amount' => '100.00',
            'insurance_amount' => '10.00',
            'total_no_of_tasks' => 1,
            'user_id' => 1,
            'form_id' => 2,
            'promo_value' => 0,
            'credits' => 0,
            'total_service_charge' => '15.00',
            'is_loader_required' => 0,
            'vehicle_id' => 1,
            'is_cod_job' => 0,
            'delivery_charge_by_buyer' => 1,
        ];

        return $this->kwikService->getEstimatedFare($task);
    }

    public function getJobDetails($orderId)
    {
        $order = Order::with('user', 'seller')->whereId($orderId)->first();
        $task = [
            'unique_order_id' => $order->kwik_order_id, //'ORDER123456',
        ];

        return $this->kwikService->getJobDetails($task);
    }
}
