<?php

namespace App\Http\Controllers;

use App\Models\Artisans;
use App\Models\ServiceRequestModel as ServiceRequest;
use App\Models\User;
use App\Models\CheckIn;
use App\Models\SubmittedQuotes;
use Illuminate\Http\Request;
use App\Notifications\CustomNotification;
use Log;

class CheckInOutController extends Controller
{
    public function clock(Request $request, $requestId)
    {
        $user = auth()->user();
        $req = ServiceRequest::whereId($requestId)->first();
        Log::debug("New request checkin && checkout", ['request' => $req]);
    
        if (!$req) {
            return get_error_response("Service request with provided ID not found", ['error' => "Service request with provided ID not found"]);
        }

        $approved_provider = $req->approved_providers_id;
        if($approved_provider == null || empty($approved_provider)) {
            // find provider using the artisan ID
            $artisan = Artisans::where('artisan_id', $req->approved_artisan_id)->first();
            if(!$artisan) {
                return get_error_response("Unable to proceed please contact support", ['error' => "Unable to proceed please contact support"]);
            }
            $approved_provider = $artisan?->service_provider_id;
            Log::info("Service request object", ['approved_provider' => $approved_provider]);
        }
    
        Log::info("Service request object", ['service_request' => $req]);
    
        $customer = User::find($req->user_id);
        $provider = User::find($approved_provider);
    
        if (!$customer) {
            Log::error("Customer not found for service request", ['request_id' => $requestId]);
        }
        if (!$provider) {
            Log::error("Provider not found for service request", ['request_id' => $requestId]);
        }
    
        $quote = SubmittedQuotes::where([
            'request_id' => $req->id,
            'provider_id' => $approved_provider
        ])->first();
    
        if (!$quote) {
            Log::error("No quote found for service request", ['request_id' => $requestId]);
            return get_error_response("Quote not found", ['error' => "No quote found for this service request"], 404);
        }
    
        $todayCheckIn = $req->checkIns()->whereDate('check_in_time', today())->latest()->first();
    
        if ($todayCheckIn && $todayCheckIn->check_out_time === null) {
            // Clock out
            $achievements = $request->input('achievements', 'No achievements specified.');
    
            $todayCheckIn->update([
                'check_out_time' => now(),
                'achievements' => $achievements,
            ]);
    
            if ($customer) {
                $customer->notify(new CustomNotification("Artisan check-out", "Artisan check-out."));
            }
            if ($provider) {
                $provider->notify(new CustomNotification("Artisan checkings are completed.", "Artisan checkings are completed."));
            }
    
            if ($req->checkIns()->whereNotNull('check_in_time')->count() >= (int) $quote->sla_duration) {
                $req->update([
                    "request_status" => "Awaiting Approval"
                ]);
            }
    
            return get_success_response([
                'message' => 'You have successfully checked out.',
                'action' => 'Clock Out',
                'check_in_time' => $todayCheckIn->check_in_time,
                'check_out_time' => $todayCheckIn->check_out_time,
                'achievements' => $achievements,
                'next_action' => 'You can clock in again tomorrow',
            ],  'You have successfully checked out.');
        } 
    
        // Clock in
        $expectedWork = $request->input('expected_work', 'No specific tasks assigned.');
    
        $newCheckIn = $req->checkIns()->create([
            'check_in_time' => now(),
            'user_id' => auth()->id(),
            'expected_work' => $expectedWork,
        ]);
    
        if ($customer) {
            $customer->notify(new CustomNotification("Artisan check-in", "Artisan check-in."));
        }
    
        return get_success_response([
            'message' => 'You have successfully checked in.',
            'action' => 'Clock In',
            'check_in_time' => $newCheckIn->check_in_time,
            'expected_work' => $expectedWork,
            'next_action' => 'Remember to clock out before leaving',
        ], 'You have successfully checked in.');
    }
    
    
    public function clockins($requestId)
    {
        $checkIns = CheckIn::where('service_request_id', $requestId)->latest()->get();
        return get_success_response($checkIns);
    }
}
