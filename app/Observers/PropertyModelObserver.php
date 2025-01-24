<?php

namespace App\Observers;

use App\Models\ApiLog;
use App\Models\Property;

class PropertyModelObserver
{
    /**
     * Handle the Property "created" event.
     */
    public function created(Property $property): void
    {        
        $activity = new APiLog();
        $activity->user_id = auth()->id();
        $activity->activity_title = "Property created";
        $activity->activity_amount = $property->total_price;
        $activity->activity_extra = [
            "property_id" => $property->id,
        ];
        $activity->save();
    }

    /**
     * Handle the Property "updated" event.
     */
    public function updated(Property $property): void
    {        
        $activity = new ApiLog();
        $activity->user_id = auth()->id();
        $activity->activity_title = "Property Updated";
        $activity->activity_amount = $property->total_price;
        $activity->activity_extra = [
            "property_id" => $property->id,
        ];
        $activity->save();
    }

    /**
     * Handle the Property "deleted" event.
     */
    public function deleted(Property $property): void
    {
        
        $activity = new APiLog();
        $activity->user_id = auth()->id();
        $activity->activity_title = "Property Deleted";
        $activity->activity_amount = $property->total_price;
        $activity->activity_extra = [
            "property_id" => $property->id,
        ];
        $activity->save();
    }

    /**
     * Handle the Property "restored" event.
     */
    public function restored(Property $property): void
    {
        
        $activity = new APiLog();
        $activity->user_id = auth()->id();
        $activity->activity_title = "Restored Deleted";
        $activity->activity_amount = $property->total_price;
        $activity->activity_extra = [
            "property_id" => $property->id,
        ];
        $activity->save();
    }

    /**
     * Handle the Property "force deleted" event.
     */
    public function forceDeleted(Property $property): void
    {
        
        $activity = new APiLog();
        $activity->user_id = auth()->id();
        $activity->activity_title = "Force Deleted";
        $activity->activity_amount = $property->total_price;
        $activity->activity_extra = [
            "property_id" => $property->id,
        ];
        $activity->save();
    }
}
