<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;

class PaymentService
{
    public function processPayment($orderId, $paymentDetails)
    {
        $order = Order::findOrFail($orderId);
        // Process payment logic here (e.g., integrate with a payment gateway)
        $payment = Payment::create([
            'order_id' => $orderId,
            'amount' => $order->item->price,
            'status' => 'completed'
        ]);
        $order->update(['status' => 'paid']);
        return $payment;
    }
}
