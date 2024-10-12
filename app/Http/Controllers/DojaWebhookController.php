<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;

class DojaWebhookController extends Controller
{
    /**
     * Handle Dojah.io verification webhook
     */
    public function handleDojahVerification(Request $request)
    {
        try {
            // Log the webhook data (optional, useful for debugging)
            Log::info('Dojah.io Webhook Data:', $request->all());

            // Verify that the payload is from Dojah (optional, if necessary)
            // You may want to check an HMAC signature or verify with a key.
            // Example: $signature = $request->header('X-Dojah-Signature');

            // Parse the incoming data
            $data = $request->all();

            // Example: Check the verification status and type
            if (isset($data['status']) && $data['status'] === 'successful') {
                // Handle successful verification
                // For example, update the user's verification status in the database
                // You may get the user ID from the webhook and update their status
                $userId = $data['user_id'];
                // Update user verification status in your database
                // User::find($userId)->update(['verification_status' => 'verified']);
            } else {
                // Handle failed verification
                $userId = $data['user_id'];
                // User::find($userId)->update(['verification_status' => 'failed']);
            }

            // Respond with 200 OK to acknowledge receipt of the webhook
            return response()->json(['message' => 'Webhook received successfully'], 200);
        } catch (\Throwable $th) {
            // Log the error
            Log::error('Error handling Dojah.io webhook:', ['error' => $th->getMessage()]);

            // Return error response
            return response()->json(['message' => 'Error processing webhook'], 400);
        }
    }
}
