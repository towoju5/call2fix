<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class VerificationWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        try {
            // Convert JSON payload into an array
            $webhookData = $request->all();

            // Extract email
            $email = data_get($webhookData, 'data.email.data.email');

            if (!$email) {
                Log::warning('Webhook received but email not found.', ['payload' => $webhookData]);
                return response()->json(['message' => 'Email not found in webhook payload'], 400);
            }

            // Find user by email
            $user = User::where('email', $email)->first();

            if (!$user) {
                Log::warning('User not found for email: ' . $email);
                return response()->json(['message' => 'User not found'], 404);
            }

            // Update user fields
            $user->update([
                'business_verification_status' => true,
                'verification_webhook_data' => $webhookData
            ]);

            Log::info('User verification status updated successfully.', ['email' => $email]);

            return response()->json(['message' => 'User verification updated successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Webhook processing failed: ' . $e->getMessage());
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }
}
