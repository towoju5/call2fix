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
            'vehicle_id' => $this->fetchVehicleId($order->product->weight),
            'is_cod_job' => 0,
        ];

        $task = [
            "domain_name" => env("KWIK_DELIVERY_DOMAIN_NAME"),
            "access_token" => $this->kwikService->getAccessToken(),
            "vendor_id" => 37,
            "is_multiple_tasks" => 1,
            "fleet_id" => "",
            "latitude" => 0,
            "longitude" => 0,
            "timezone" => -330,
            "has_pickup" => 1,
            "has_delivery" => 1,
            "pickup_delivery_relationship" => 0,
            "layout_type" => 0,
            "auto_assignment" => 1,
            "team_id" => "",
            "pickups" => [
                [
                    "address" => "Sector 28, Chandigarh, India",
                    "name" => "Ishita",
                    "latitude" => 30.7172888,
                    "longitude" => 76.8035087,
                    "time" => "2023-08-22 15:25:23",
                    "phone" => "+919898989898",
                    "email" => ""
                ]
            ],
            "deliveries" => [
                [
                    "address" => "Sector 32, Chandigarh, India",
                    "name" => "Ishita",
                    "latitude" => 30.709472,
                    "longitude" => 76.7743709,
                    "time" => "2023-08-22T15:33:53.000Z",
                    "phone" => "+919898989898",
                    "email" => "",
                    "has_return_task" => false,
                    "is_package_insured" => 0,
                    "hadVairablePayment" => 1,
                    "hadFixedPayment" => 0,
                    "is_task_otp_required" => 0
                ]
            ],
            "insurance_amount" => 0,
            "total_no_of_tasks" => 1,
            "total_service_charge" => 0,
            "payment_method" => 524288,
            "amount" => "1320.2",
            "surge_cost" => 0,
            "surge_type" => 0,
            "delivery_instruction" => "",
            "loaders_amount" => 0,
            "loaders_count" => 0,
            "is_loader_required" => 0,
            "delivery_images" => "",
            "vehicle_id" => 1,
            "sareaId" => "6"
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

    public function fetchVehicleId($weight)
    {
        if ($weight < 100) {
            $size = 1;
        }
        $task = [
            'is_vendor' => 1,
            'size' => 1, // 0 for bike, 1 for small, 2 for medium, 3 for large
        ];

        return 1;

        // return $this->kwikService->fetchVehicleId($task);
    }

    public function calculatePricing($orderId)
    {
        $order = Order::with('user', 'seller', 'product')->whereId($orderId)->first();
        $task = [
            "custom_field_template" => "pricing-template",
            "access_token" => $this->kwikService->getAccessToken(),
            "domain_name" => env('KWIK_DELIVERY_DOMAIN_NAME'),
            "timezone" => 1,
            "vendor_id" => env('KWIK_DELIVERY_VENDOR_ID', 151),
            "is_multiple_tasks" => 1,
            "layout_type" => 0,
            "pickup_custom_field_template" => "pricing-template",
            "deliveries" => [
                [
                    'address' => $order->delivery_address,
                    'email' => $order->user->email,
                    'phone' => $order->user->phone,
                    'latitude' => $order->delivery_latitude,
                    'longitude' => $order->delivery_longitude,
                    'has_return_task' => false,
                ]
            ],
            "has_pickup" => 1,
            "has_delivery" => 1,
            "auto_assignment" => 1,
            "user_id" => 1,
            "pickups" => [
                [
                    'address' => $order->product->product_location,
                    'email' => $order->seller->email,
                    'phone' => $order->seller->phone,
                    'latitude' => $order->product->latitude ?? '40.7128',
                    'longitude' => $order->product->longitude ?? '-74.0060',
                ]
            ],
            "payment_method" => 32,
            "form_id" => 2,
            "vehicle_id" => $this->fetchVehicleId($order->weight),
            "is_loader_required" => 1,
            "loaders_amount" => 40,
            "loaders_count" => 4,
            "is_cod_job" => 1,
            "parcel_amount" => 1000
        ];

        $response = $this->kwikService->calculatePricing($task);
        if(isset($response['data']['per_task_cost'])) {
            return $response['data']['per_task_cost'];
        }

        return 0;
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
