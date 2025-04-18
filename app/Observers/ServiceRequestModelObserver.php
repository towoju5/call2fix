<?php

namespace App\Observers;

use App\Models\ApiLog;
use App\Models\ServiceRequest;

class ServiceRequestModelObserver
{
    /**
     * Handle the ServiceRequest "created" event.
     */
    public function created(ServiceRequest $serviceRequest): void
    {        
        $activity = new APiLog();
        $activity->user_id = auth()->id();
        $activity->activity_title = "Service Request created";
        $activity->activity_amount = $serviceRequest->total_price;
        $activity->activity_extra = [
            "ServiceRequest_id" => $serviceRequest->id,
            "txn_type" => "debit"
        ];
        $activity->save();
    }

    /**
     * Handle the ServiceRequest "updated" event.
     */
    public function updated(ServiceRequest $serviceRequest): void
    {        
        $activity = new ApiLog();
        $activity->user_id = auth()->id();
        $activity->activity_title = "Service Request updated";
        $activity->activity_amount = $serviceRequest->total_price;
        $activity->activity_extra = [
            "ServiceRequest_id" => $serviceRequest->id,
        ];
        $activity->save();
    }

    /**
     * Handle the ServiceRequest "deleted" event.
     */
    public function deleted(ServiceRequest $serviceRequest): void
    {        
        $activity = new APiLog();
        $activity->user_id = auth()->id();
        $activity->activity_title = "Service Request deleted";
        $activity->activity_amount = $serviceRequest->total_price;
        $activity->activity_extra = [
            "ServiceRequest_id" => $serviceRequest->id,
        ];
        $activity->save();
    }

    /**
     * Handle the ServiceRequest "restored" event.
     */
    public function restored(ServiceRequest $serviceRequest): void
    {        
        $activity = new APiLog();
        $activity->user_id = auth()->id();
        $activity->activity_title = "Service Request restored";
        $activity->activity_amount = $serviceRequest->total_price;
        $activity->activity_extra = [
            "ServiceRequest_id" => $serviceRequest->id,
        ];
        $activity->save();
    }

    /**
     * Handle the ServiceRequest "force deleted" event.
     */
    public function forceDeleted(ServiceRequest $serviceRequest): void
    {        
        $activity = new APiLog();
        $activity->user_id = auth()->id();
        $activity->activity_title = "Service Request forceDeleted";
        $activity->activity_amount = $serviceRequest->total_price;
        $activity->activity_extra = [
            "ServiceRequest_id" => $serviceRequest->id,
        ];
        $activity->save();
    }
}
