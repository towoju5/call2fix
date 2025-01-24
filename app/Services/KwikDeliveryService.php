<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Log;

class KwikDeliveryService
{
    private $baseUrl;
    private $accessToken;
    private $vendorId;

    public function __construct()
    {
        $this->baseUrl = env('KWIK_DELIVERY_URL', 'https://staging-api-test.kwik.delivery');
        $this->accessToken = $this->getAccessToken();
        $this->vendorId = config('services.kwik.vendor_id');
    }

    public function getAccessToken()
    {
        $cacheKey = 'kwik_delivery_access_token';
        $cacheDuration = 5 * 60; // 20 minutes in seconds

        return cache()->remember($cacheKey, $cacheDuration, function () {
            $response = Http::post($this->baseUrl . '/vendor_login', [
                'email' => env('KWIK_DELIVERY_EMAIL', 'corp-it@alphamead.com'),
                'password' => env('KWIK_DELIVERY_PASSWORD', 'Alph@mead24'),
                'api_login' => 1,
                'domain_name' => env('KWIK_DELIVERY_DOMAIN_NAME', 'staging-client-panel.kwik.delivery'),
            ]);
            return $response->json()['data']['access_token'] ?? null;
        });
    }

    public function createPickupAndDeliveryTask($data)
    {
        return $this->makeRequest('post', 'create_task', $data);
    }

    public function createReturnTask($data)
    {
        return $this->makeRequest('post', 'create_task', $data);
    }

    public function cancelTask($data)
    {
        return $this->makeRequest('post', '/cancel_task', $data);
    }

    public function fetchVehicleId($data)
    {
        return $this->makeRequest('post', '/fetch_vehicle_id', $data);
    }

    public function calculatePricing($data)
    {
        return $this->makeRequest('post', '/send_payment_for_task', $data);
    }

    public function getEstimatedFare($data)
    {
        return $this->makeRequest('post', '/get_bill_breakdown', $data);
    }

    public function getJobDetails($uniqueOrderId)
    {
        return $this->makeRequest('get', '/get_job_details', [
            'unique_order_id' => $uniqueOrderId,
        ]);
    }

    private function makeRequest($method, $endpoint, $data = [])
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
        ])->$method("{$this->baseUrl}$endpoint", $data);

        return $this->handleApiResponse($response);
    }

    private function handleApiResponse($response)
    {
        $statusCode = $response->status();
        $data = $response->json();

        switch ($statusCode) {
            case 200:
                return $data;
            case 100:
                throw new \Exception('Parameter missing', 100);
            case 101:
                throw new \Exception('Invalid key', 101);
            case 201:
                throw new \Exception($data['message'] ?? 'Error occurred', 201);
            case 404:
                throw new \Exception('Error in execution', 404);
            default:
                throw new \Exception('Unknown error occurred', $statusCode);
        }
    }

    public function getTaskStatus($statusCode)
    {
        $statuses = [
            0 => 'Upcoming',
            1 => 'Started',
            2 => 'Ended',
            3 => 'Failed',
            4 => 'Arrived',
            6 => 'Unassigned',
            7 => 'Accepted',
            8 => 'Declined',
            9 => 'Cancelled',
            10 => 'Deleted',
        ];

        return $statuses[$statusCode] ?? 'Unknown';
    }
}
