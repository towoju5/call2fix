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
        $this->paystackSecretKey = config('services.paystack.secret_key');
    }

    public function paystackWebhook(Request $request)
    {
        return $this->handleWebhook($request);
    }

    /**
     * Handle Paystack webhook
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleWebhook(Request $request)
    {
        try {
            if (!$this->isValidRequest($request)) {
                Log::warning('Invalid Paystack webhook request received');
                return response()->json(['error' => 'Invalid request'], 400);
            }

            $input = $request->getContent();
            $event = json_decode($input, true);

            // if (!$this->isValidSignature($input)) {
            //     Log::warning('Invalid Paystack signature');
            //     return response()->json(['error' => 'Invalid signature'], 400);
            // }

            $paystack = new PaystackServices();
            $eventType = $event['event'];

            Log::info("Processing Paystack webhook event: {$eventType}");
            Log::info("Paystack webhook event content:", $event);

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
            return response()->json(['message' => 'Webhook processed successfully']);
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
