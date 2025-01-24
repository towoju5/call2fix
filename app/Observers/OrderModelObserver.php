<?php

namespace App\Observers;

use App\Models\ApiLog;
use App\Models\Order;

class OrderModelObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {        
        $activity = new APiLog();
        $activity->user_id = auth()->id();
        $activity->activity_title = "Order created";
        $activity->activity_amount = $order->total_price;
        $activity->activity_extra = [
            "order_id" => $order->id,
        ];
        $activity->save();
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        
        $activity = new APiLog();
        $activity->user_id = auth()->id();
        $activity->activity_title = "Order updated";
        $activity->activity_amount = $order->total_price;
        $activity->activity_extra = [
            "order_id" => $order->id,
        ];
        $activity->save();
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        
        $activity = new APiLog();
        $activity->user_id = auth()->id();
        $activity->activity_title = "Order Deleted";
        $activity->activity_amount = $order->total_price;
        $activity->activity_extra = [
            "order_id" => $order->id,
        ];
        $activity->save();
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        
        $activity = new APiLog();
        $activity->user_id = auth()->id();
        $activity->activity_title = "Order restored";
        $activity->activity_amount = $order->total_price;
        $activity->activity_extra = [
            "order_id" => $order->id,
        ];
        $activity->save();
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        
        $activity = new APiLog();
        $activity->user_id = auth()->id();
        $activity->activity_title = "Order Deleted";
        $activity->activity_amount = $order->total_price;
        $activity->activity_extra = [
            "order_id" => $order->id,
        ];
        $activity->save();
    }
}
