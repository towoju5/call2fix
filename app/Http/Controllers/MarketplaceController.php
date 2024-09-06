<?php

namespace App\Http\Controllers;

use App\Services\MarketplaceService;
use App\Services\PaymentService;
use App\Services\TrackingService;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    protected $marketplaceService;
    protected $paymentService;
    protected $trackingService;

    public function __construct(
        MarketplaceService $marketplaceService,
        PaymentService $paymentService,
        TrackingService $trackingService
    ) {
        $this->marketplaceService = $marketplaceService;
        $this->paymentService = $paymentService;
        $this->trackingService = $trackingService;
    }

    public function browseItems(Request $request)
    {
        $category = $request->input('category');
        $response = $this->marketplaceService->getItems($category);
        return get_success_response($response, "Record retrieved successfully");
    }

    public function ItemDetails(Request $request)
    {
        $category = $request->input('category');
        $response = $this->marketplaceService->getItems($category);
        return get_success_response($response, "Record retrieved successfully");
    }

    public function purchaseItem(Request $request)
    {
        $itemId = $request->input('item_id');
        $userId = $request->user()->id;
        $response = $this->marketplaceService->purchaseItem($itemId, $userId);
        if(isset($response['error'])) {
            return get_error_response($response['error'], ['error' => $response['error']]);
        }
        return get_success_response($response, "Record retrieved successfully");
    }

    public function requestItem(Request $request)
    {
        $itemDetails = $request->input('item_details');
        $userId = $request->user()->id;
        $response = $this->marketplaceService->requestItem($itemDetails, $userId);
        return get_success_response($response, "Record retrieved successfully");
    }

    public function payForProduct(Request $request)
    {
        $orderId = $request->input('order_id');
        $paymentDetails = $request->input('payment_details');
        $response = $this->paymentService->processPayment($orderId, $paymentDetails);
        return get_success_response($response, "Record retrieved successfully");
    }

    public function trackOrder(Request $request)
    {
        $orderId = $request->input('order_id');
        $response = $this->trackingService->trackOrder($orderId);
        return get_success_response($response, "Record retrieved successfully");
    }

    public function sellItem(Request $request)
    {
        $itemDetails = $request->input('item_details');
        $sellerId = $request->user()->id;
        $response = $this->marketplaceService->listItemForSale($itemDetails, $sellerId);
        return get_success_response($response, "Record retrieved successfully");
    }
}
