<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CheckInOutController extends Controller
{
    public function clock()
    {
        $user = auth()->user();
        $todayCheckIn = $user->checkIns()->whereDate('check_in_time', today())->first();

        if ($todayCheckIn) {
            if ($todayCheckIn->check_out_time === null) {
                $todayCheckIn->update([
                    'check_out_time' => now(),
                ]);

                $duration = $todayCheckIn->check_in_time->diffInHours($todayCheckIn->check_out_time);

                return get_success_response([
                    'action' => 'Clock Out',
                    'check_in_time' => $todayCheckIn->check_in_time->format('Y-m-d H:i:s'),
                    'check_out_time' => $todayCheckIn->check_out_time->format('Y-m-d H:i:s'),
                    'duration' => $duration . ' hours',
                    'next_action' => 'You can clock in again tomorrow'
                ]);
            } else {
                return get_error_response([
                    'action' => 'No Action',
                    'check_in_time' => $todayCheckIn->check_in_time->format('Y-m-d H:i:s'),
                    'check_out_time' => $todayCheckIn->check_out_time->format('Y-m-d H:i:s'),
                    'next_action' => 'You can clock in again tomorrow'
                ]);
            }
        } else {
            $newCheckIn = $user->checkIns()->create([
                'check_in_time' => now(),
            ]);

            return get_success_response([
                'action' => 'Clock In',
                'check_in_time' => $newCheckIn->check_in_time->format('Y-m-d H:i:s'),
                'next_action' => 'Remember to clock out before leaving'
            ]);
        }
    }
}
