<?php

namespace App\Observers;

use App\Models\ApiLog;
use App\Models\WalletTransaction as Wallet;

class WalletModelObserver
{
    /**
     * Handle the Wallet "created" event.
     */
    public function created(Wallet $wallet): void
    {      
        $activity = new ApiLog();
        $activity->user_id = auth()->id();
        $activity->activity_title = "Wallet created";
        $activity->activity_amount = $wallet->total_price;
        $activity->activity_extra = [
            "wallet_id" => $wallet->id,
        ];
        $activity->save();
    }

    /**
     * Handle the Wallet "updated" event.
     */
    public function updated(Wallet $wallet): void
    {      
        $activity = new ApiLog();
        $activity->user_id = auth()->id();
        $activity->activity_title = "wallet updated";
        $activity->activity_amount = $wallet->total_price;
        $activity->activity_extra = [
            "wallet_id" => $wallet->id,
        ];
        $activity->save();
    }

    /**
     * Handle the Wallet "deleted" event.
     */
    public function deleted(Wallet $wallet): void
    {      
        $activity = new ApiLog();
        $activity->user_id = auth()->id();
        $activity->activity_title = "wallet deleted";
        $activity->activity_amount = $wallet->total_price;
        $activity->activity_extra = [
            "wallet_id" => $wallet->id,
        ];
        $activity->save();
    }

    /**
     * Handle the Wallet "restored" event.
     */
    public function restored(Wallet $wallet): void
    {      
        $activity = new ApiLog();
        $activity->user_id = auth()->id();
        $activity->activity_title = "wallet restored";
        $activity->activity_amount = $wallet->total_price;
        $activity->activity_extra = [
            "wallet_id" => $wallet->id,
        ];
        $activity->save();
    }

    /**
     * Handle the Wallet "force deleted" event.
     */
    public function forceDeleted(Wallet $wallet): void
    {      
        $activity = new ApiLog();
        $activity->user_id = auth()->id();
        $activity->activity_title = "Wallet forceDeleted";
        $activity->activity_amount = $wallet->total_price;
        $activity->activity_extra = [
            "wallet_id" => $wallet->id,
        ];
        $activity->save();
    }
}
