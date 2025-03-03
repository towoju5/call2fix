<?php

namespace App\Http\Controllers;

use App\Models\ApiLog;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class EventsController extends Controller
{
    public function index()
    {
        try {
            $activity = ApiLog::where('user_id', auth()->id())->latest()->limit(20)->get();
            return get_success_response($activity);
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }

    public function show($eventId)
    {
        try {
            $activity = ApiLog::where('user_id', auth()->id())->whereId($eventId)->latest()->first();

            if(!$activity) {
                return get_error_response("Event not found", ['error' => 'Event not found'], 404);
            }
    
            return get_success_response($activity);
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }
}