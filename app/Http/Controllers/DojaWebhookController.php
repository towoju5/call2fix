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
    
    public function sendSMS($recipient = "+2349039395114", $message = "Your OTP code 123456 is valid for 10 minutes")
    {
        try {
            $dojahApiKey = env('DOJAH_API_KEY', 'test_sk_df6LCCYuIxF1GGcEP5xLyoFPD');
            $dojahAppId = env('DOJAH_APP_ID', '6707c1476426a6e441469674');

            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', env("DOJA_BASE_URL", "https://sandbox.dojah.io/api/v1") . "/messaging/sms", [
                'headers' => [
                    'Authorization' => $dojahApiKey,
                    'AppId' => $dojahAppId,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'to' => $recipient,
                    'message' => $message,
                    'channel' => 'whatsapp',
                    'sender_id' => env('DOJAH_SENDER_ID', 'Call2Fix'),
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody(), true);

            if ($statusCode == 200 && isset($responseBody['status']) && $responseBody['status'] === "SMS sent successfully") {
                Log::info('OTP SMS sent successfully', ['phone' => $recipient]);
                return response()->json(['message' => 'SMS sent successfully'], 200);
            } else {
                Log::error('Failed to send OTP SMS', ['response' => $responseBody]);
                return response()->json(['message' => 'Failed to send SMS'], 400);
            }
        } catch (\Throwable $th) {
            Log::error('Error sending OTP SMS', ['error' => $th->getMessage()]);
            return response()->json(['message' => 'Error sending SMS'], 500);
        }
    }

    public function generateDojaVerificationUrl()
    {
        // Implement URL generation logic here
    }
}
