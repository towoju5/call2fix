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
            $webhookData = $request->all();
            Log::info("Incoming webhook from Dojah: ", ["payload" => $webhookData]);
            $email = data_get($webhookData, 'data.email.data.email');

            if (!$email) {
                Log::warning('Webhook received but email not found.', ['payload' => $webhookData]);
                return response()->json(['message' => 'Email not found in webhook payload'], 400);
            }

            $user = User::where('email', $email)->first();

            if (!$user) {
                Log::warning('User not found for email: ' . $email);
                return response()->json(['message' => 'User not found'], 404);
            }

            $bvnData = data_get($webhookData, 'data.government_data.data.bvn.entity', []);

            $user->update([
                'first_name' => data_get($bvnData, 'first_name') ?? $user->first_name,
                'last_name' => data_get($bvnData, 'last_name') ?? $user->last_name,
                'phone' => data_get($bvnData, 'phone_number1') ?? $user->phone,
                // 'gender' => data_get($bvnData, 'gender') ?? $user->gender,
                // 'date_of_birth' => data_get($bvnData, 'date_of_birth') ?? $user->date_of_birth,
                // 'profile_picture' => data_get($bvnData, 'image_url') ?? $user->profile_picture,
                // 'country' => data_get($webhookData, 'data.countries.data.country') ?? $user->country,
                'business_verification_status' => true,
                'verification_webhook_data' => $webhookData
            ]);

            $businessData = data_get($webhookData, 'data.business_data', []);

            if (!empty($businessData)) {
                $user->business()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'businessName' => data_get($businessData, 'business_name') ?? optional($user->business)->businessName,
                        'cacNumber' => data_get($businessData, 'business_number') ?? optional($user->business)->cacNumber,
                        'businessIdType' => data_get($webhookData, 'id_type') ?? optional($user->business)->businessIdType,
                        'businessIdNumber' => data_get($webhookData, 'verification_value') ?? optional($user->business)->businessIdNumber,
                    ]
                );
            }

            Log::info('User and business verification data updated successfully.', ['email' => $email]);

            return response()->json(['message' => 'User and business data updated successfully'], 200);

        } catch (\Exception $e) {
            Log::error('Webhook processing failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }
}
