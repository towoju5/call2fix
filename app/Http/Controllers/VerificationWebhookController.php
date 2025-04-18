<?php

namespace App\Http\Controllers;

use App\Models\Artisans;
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
            $bvnData = data_get($webhookData, 'data.government_data.data.nin.entity', []);
            
            $user->update([
                'first_name' => data_get($bvnData, 'first_name') ?? $user->first_name,
                'last_name' => data_get($bvnData, 'last_name') ?? $user->last_name,
                'business_verification_status' => true,
                'verification_webhook_data' => $webhookData
            ]);

            if($user->current_role == 'artisan') {
                $artisan = Artisans::where('artisan_id', $user->id)->first();
                if($artisan) {
                    $artisan->update([
                        "first_name" => data_get($bvnData, 'first_name') ?? $user->first_name,,
                        "last_name" => data_get($bvnData, 'last_name') ?? $user->last_name,
                    ]);
                }
            }


            $businessData = data_get($webhookData, 'data.business_data', []);
            $business_number = data_get($businessData, 'business_number');
            $cacNumber = optional($user->business_info)->cacNumber ?? $user->business_info->cacNumber ?? null;

            if ($business_number !== $cacNumber) {
                Log::info("RC number does not match {$business_number} is not the same as {$cacNumber}");
                $user->notify(new CustomNotification(
                    "Verification Unsuccessful",
                    "We were unable to verify your business due to a mismatch in the submitted registration number. Kindly review your provided details and try again. If the issue persists, please contact support for assistance."
                ));
            
                return false;
            }
            


            if (!empty($businessData) && isset($businessData['business_number'])) {
                $user->business_info()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'businessName' => data_get($businessData, 'business_name') ?? optional($user->business_info)->businessName ?? null,
                        'cacNumber' => data_get($businessData, 'business_number') ?? optional($user->business_info)->cacNumber ?? null,
                        'businessIdType' => data_get($webhookData, 'id_type') ?? optional($user->business_info)->businessIdType ?? null,
                        'businessIdNumber' => data_get($webhookData, 'verification_value') ?? optional($user->business_info)->businessIdNumber ?? null,
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
