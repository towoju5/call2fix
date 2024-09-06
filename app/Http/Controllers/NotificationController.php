<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{

    public function index()
    {
        try {
            $notifications = auth()->user()->notifications;
            return get_success_response($notifications, 'Notifications fetched successfully.', );
        } catch (\Exception $e) {
            return get_error_response('An error occurred while fetching categories.', ['error' => $e->getMessage()], 500);
        }
    }

    public function markAsRead($id)
    {
        try {
            $notification = auth()->user()->notifications()->findOrFail($id);
            $notification->markAsRead();
            return get_success_response(null, 'Notification marked as read', 200);
        } catch (\Exception $e) {
            return get_error_response('An error occurred while fetching categories.', ['error' => $e->getMessage()], 500);
        }
    }

    public function markAllAsRead()
    {
        try {
            auth()->user()->unreadNotifications->markAsRead();
            return get_success_response(null, 'All notifications marked as read', 200);
        } catch (\Exception $e) {
            return get_error_response('An error occurred while fetching categories.', ['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $notification = auth()->user()->notifications()->findOrFail($id);
            $notification->delete();
            return get_success_response(null, 'Notification deleted', 200);
        } catch (\Exception $e) {
            return get_error_response('An error occurred while fetching categories.', ['error' => $e->getMessage()], 500);
        }
    }

    public function destroyAll()
    {
        try {
            if(auth()->user()->notifications()->delete()){
                return get_success_response(null, 'All notifications deleted', 200);
            }
            return get_error_response('An error occurred while fetching categories.', ['error' => 'An error occurred while deleting notifications'], 500);
        } catch (\Exception $e) {
            return get_error_response('An error occurred while fetching categories.', ['error' => $e->getMessage()], 500);
        }
    }

}
