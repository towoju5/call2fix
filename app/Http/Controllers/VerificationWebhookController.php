<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\CustomNotification;
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
            
            $businessData = data_get($webhookData, 'data.business_data', []);

            if (data_get($businessData, 'business_number') !== optional($user->business)->cacNumber) {
                if ($user && $user->business) {
                    $user->notify(new CustomNotification(
                        "Verification Unsuccessful",
                        "We were unable to verify your business due to a mismatch in the submitted registration number. Kindly review your provided details and try again. If the issue persists, please contact support for assistance."
                    ));
                }
            
                return false;
            }
            

            $bvnData = data_get($webhookData, 'data.government_data.data.bvn.entity', []);
            
            $user->update([
                'first_name' => data_get($bvnData, 'first_name') ?? $user->first_name,
                'last_name' => data_get($bvnData, 'last_name') ?? $user->last_name,
                'business_verification_status' => true,
                'verification_webhook_data' => $webhookData
            ]);


            if (!empty($businessData)) {
                $user->business_info()->updateOrCreate(
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

            $user->notify(new CustomNotification('Verification Completed', "Your profile verification has been completed successfully, please relogin into your account to proceed"));

            return get_success_response(['message' => 'User and business data updated successfully'], "'User and business data updated successfully'", 200);

        } catch (\Exception $e) {
            Log::error('Webhook processing failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return get_error_response($e->getMessage(), ['message' => 'Internal Server Error'], 500);
        }
    }
}
