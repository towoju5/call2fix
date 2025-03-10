<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class CheckInOutController extends Controller
{
    public function clock($requestId, Request $request)
    {
        $user = auth()->user();
        $req = ServiceRequest::whereId($requestId)->first();

        // Handle case where Service Request is not found
        if(!$req) {
            return get_error_response('Service Request not found', ['error' => 'Service Request not found'], 404);
        }

        if($req->status != "pending" OR $req->status != "rework") {
            return get_error_response("Request can not be completed at the momment", ['error' => "Request can not accept clock in/out at the moment"]);
        }

        // Find today's check-in record for the service request
        $todayCheckIn = $req->checkIns()->whereDate('check_in_time', today())->first();

        // If check-in for today exists
        if ($todayCheckIn) {
            // If the user hasn't clocked out yet, clock out and calculate the duration
            if ($todayCheckIn->check_out_time === null) {
                // Capture what was achieved during the session from the request
                $achievements = $request->input('achievements', 'No achievements specified.');

                $todayCheckIn->update([
                    'check_out_time' => now(),
                    'achievements' => $achievements, // Add achievements to the check-out
                ]);

                $duration = $todayCheckIn->check_in_time->diffInHours($todayCheckIn->check_out_time);

                // Check-out success message
                return get_success_response([
                    'message' => 'You have successfully checked out.',
                    'action' => 'Clock Out',
                    'check_in_time' => $todayCheckIn->check_in_time->format('Y-m-d H:i:s'),
                    'check_out_time' => $todayCheckIn->check_out_time->format('Y-m-d H:i:s'),
                    'duration' => $duration . ' hours',
                    'achievements' => $achievements,
                    'next_action' => 'You can clock in again tomorrow',
                ],  'You have successfully checked out.');
            } else {
                // If already clocked out for today, show appropriate message
                return get_error_response('You have already checked out for today.', [
                    'message' => 'You have already checked out for today.',
                    'action' => 'No Action',
                    'check_in_time' => $todayCheckIn->check_in_time->format('Y-m-d H:i:s'),
                    'check_out_time' => $todayCheckIn->check_out_time->format('Y-m-d H:i:s'),
                    'next_action' => 'You can clock in again tomorrow',
                ]);
            }
        } else {
            // If no check-in exists for today, clock in
            // Capture the note describing what is expected to be done
            $expectedWork = $request->input('expected_work', 'No specific tasks assigned.');

            $newCheckIn = $user->checkIns()->create([
                'check_in_time' => now(),
                'expected_work' => $expectedWork, // Add expected work note at check-in
            ]);

            // Check-in success message
            return get_success_response([
                'message' => 'You have successfully checked in.',
                'action' => 'Clock In',
                'check_in_time' => $newCheckIn->check_in_time->format('Y-m-d H:i:s'),
                'expected_work' => $expectedWork,
                'next_action' => 'Remember to clock out before leaving',
            ], 'You have successfully checked in.');
        }
    }
}
