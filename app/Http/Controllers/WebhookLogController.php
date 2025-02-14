<?php

namespace App\Http\Controllers;

use App\Models\TransactionRecords;
use App\Models\User;
use App\Services\PaystackServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookLogController extends Controller
{
    protected $paystackSecretKey;
    protected $paystackBaseUrl = 'https://api.paystack.co';

    public function __construct()
    {
        $this->paystackSecretKey = get_settings_value('paystack_secret_key', 'sk_test_390011d63d233cad6838504b657721883bc096ec');;
    }

    public function paystackWebhook(Request $request)
    {
        return $this->handleWebhook($request);
    }

    public function callback()
    {
        return view('paystack');
    }

    /**
     * Handle Paystack webhook
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleWebhook(Request $request)
    {
        try {
            // If the request is a GET request, just return the view
            if ($request->isMethod('get')) {
                return view('paystack');
            }
    
            $input = $request->getContent();
            $event = json_decode($input, true);
    
            // If the event is null or not an array, log and silently continue
            if (!is_array($event) || !isset($event['event'])) {
                Log::warning('Received invalid or empty Paystack webhook event', ['payload' => $input]);
                http_response_code(200); // Acknowledge the webhook to prevent retries
                return;
            }
    
            $paystack = new PaystackServices();
            $eventType = $event['event'];
    
            Log::info("Processing Paystack webhook event: {$eventType}");
            Log::info("Paystack webhook event content:", $event);
    
            // Ensure 'data' exists before passing it to handlers
            if (!isset($event['data'])) {
                Log::warning("Missing 'data' field in Paystack event: {$eventType}");
                http_response_code(200);
                return;
            }
    
            switch ($eventType) {
                case 'charge.success':
                    $paystack->handleSuccessfulCharge($event['data']);
                    break;
                case 'transfer.success':
                    $paystack->handleSuccessfulTransfer($event['data']);
                    break;
                case 'dedicated_account.assign.success':
                    $paystack->handleVirtualAccountCreation($event['data']);
                    break;
                default:
                    Log::info("Unhandled event type: {$eventType}");
                    break;
            }
    
            Log::info("Paystack webhook processed successfully: {$eventType}");
            http_response_code(200); // Respond with success to Paystack
        } catch (\Exception $e) {
            Log::error('Paystack Webhook Error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
    
    /**
     * Check if the request is valid
     * @param Request $request
     * @return bool
     */
    private function isValidRequest(Request $request): bool
    {
        return $request->isMethod('post') && $request->hasHeader('X-Paystack-Signature');
    }

    /**
     * Validate the Paystack signature
     * @param string $input
     * @return bool
     */
    private function isValidSignature(string $input): bool
    {
        $calculatedSignature = hash_hmac('sha512', $input, $this->paystackSecretKey);
        $paystackSignature = request()->header('X-Paystack-Signature');

        return hash_equals($calculatedSignature, $paystackSignature);
    }
}
