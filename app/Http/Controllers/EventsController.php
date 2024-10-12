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
            $activity = ApiLog::where('user_id', auth()->id())->latest()->paginate(per_page());
            return paginate_yativo($activity);
        } catch (\Throwable $th) {
            return get_error_response(['error' => $th->getMessage()]);
        }
    }

    public function show($eventId)
    {
        try {
            $activity = ApiLog::where('user_id', auth()->id())->whereId($eventId)->latest()->first();

            if(!$activity) {
                return get_error_response(['error' => 'Event not found'], 404);
            }
    
            return get_success_response($activity);
        } catch (\Throwable $th) {
            return get_error_response(['error' => $th->getMessage()]);
        }
    }
}