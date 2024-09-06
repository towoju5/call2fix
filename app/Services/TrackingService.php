<?php

namespace App\Services;

use App\Models\Order;

class TrackingService
{
    public function trackOrder($orderId)
    {
        $order = Order::findOrFail($orderId);
        // Implement order tracking logic here
        return $order->toArray();
    }
}
