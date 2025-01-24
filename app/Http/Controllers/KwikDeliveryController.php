<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
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

    public function createPickupAndDeliveryTask($deliveryAddress, $deliveryLatitude, $deliveryLongitude, Product $product, User $seller, User $user)
    {
        $is_pickup = $product->delivery_type === 'home_delivery';
        $task = [
            "domain_name" => env("KWIK_DELIVERY_DOMAIN_NAME"),
            "access_token" => $this->kwikService->getAccessToken(),
            "vendor_id" => 37,
            "is_multiple_tasks" => 1,
            "fleet_id" => "",
            "latitude" => 0,
            "longitude" => 0,
            "timezone" => -330,
            "has_pickup" => $is_pickup,
            "has_delivery" => 1,
            "pickup_delivery_relationship" => 0,
            "layout_type" => 0,
            "auto_assignment" => 1,
            "team_id" => "",
            "pickups" => [
                [
                    'address' => $product->product_location,
                    'latitude' => $product->product_latitude,
                    'longitude' => $product->product_longitude,
                    "name" => "{$seller->first_name} {$seller->last_name}",
                    'email' => $seller->email,
                    'phone' => $seller->phone,
                ]
            ],
            "deliveries" => [
                [
                    'address' => $deliveryAddress,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'latitude' => $deliveryLatitude,
                    'longitude' => $deliveryLongitude,
                    "name" => "{$user->first_name} {$user->last_name}",
                    'has_return_task' => false,
                ],
            ],
            "insurance_amount" => 0,
            "total_no_of_tasks" => 1,
            "total_service_charge" => 0,
            "payment_method" => 524288,
            // "amount" => "1320.2",
            "surge_cost" => 0,
            "surge_type" => 0,
            "delivery_instruction" => "",
            "loaders_amount" => 0,
            "loaders_count" => 0,
            "is_loader_required" => 0,
            "delivery_images" => "",
            "vehicle_id" => self::fetchVehicleId($product->weight),
            // "sareaId" => "6"
        ];
        
        Log::error("Kwik Delivery response: ", ["response" => $task]);

        $result = $this->kwikService->createPickupAndDeliveryTask($task);

        Log::error("Kwik Delivery response: ", ["response" => $result, "payload" => $task]);
        // kwik_order_id
        
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
        // all weights are in kg
        if ($weight <= 20) {
            $size = 0; // Bike
        } elseif ($weight <= 200) {
            $size = 1; // Small Vehicle
        } elseif ($weight <= 5000) {
            $size = 2; // Medium Vehicle
        } else {
            $size = 3; // Large Vehicle
        }
    
        // Task to send to Kwik service
        $task = [
            'is_vendor' => 1,
            'size' => $size, // 0 for bike, 1 for small, 2 for medium, 3 for large
        ];
    
        // Optionally, if you want to fetch the actual vehicle ID from a service:
        // $response = $this->kwikService->fetchVehicleId($task);
        // Log the response or any relevant information from the response
        // Log::info("Fetch vehicle Id response: ", $response);
    
        // If you're simply returning the size (vehicle type):
        return $size;
    }

    public function calculatePricing($deliveryAddress, $deliveryLatitude, $deliveryLongitude, Product $product, User $seller, User $user, $checkOnly = false)
    {
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
                    'address' => $deliveryAddress,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'latitude' => $deliveryLatitude,
                    'longitude' => $deliveryLongitude,
                    'has_return_task' => false,
                ]
            ],
            "has_pickup" => 1,
            "has_delivery" => 1,
            "auto_assignment" => 1,
            "user_id" => 1,
            "pickups" => [
                [
                    'address' => $product->product_location,
                    'email' => $seller->email,
                    'phone' => $seller->phone,
                    'latitude' => $product->product_latitude,
                    'longitude' => $product->product_longitude,
                ]
            ],
            "payment_method" => 32,
            "form_id" => 2,
            "vehicle_id" => $this->fetchVehicleId($product->weight),
            "is_loader_required" => 0,
            "loaders_amount" => 0,
            "loaders_count" => 0,
            "is_cod_job" => 0,
            "parcel_amount" => $product->price
        ];

        $response = $this->kwikService->calculatePricing($task);
        Log::info("Kwik Delivery response: ", ["response" => $response, "payload" => $task]);
        if (isset($response['data']['per_task_cost'])) {
            if($checkOnly == true) {
                return $response['data'];
            }
            return $response['data']['per_task_cost'];
        }

        return ['error' => $response['message']];
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
