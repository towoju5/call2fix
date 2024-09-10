<?php

namespace App\Http\Controllers;

use App\Models\TransactionRecords;
use App\Models\User;
use App\Services\PaystackServices;
use Illuminate\Http\Request;

class WebhookLogController extends Controller
{
    protected $paystackSecretKey;
    protected $paystackBaseUrl = 'https://api.paystack.co';

    public function __construct()
    {
        $this->paystackSecretKey = getenv("PAYSTACK_SECRET_KEY");
    }

    /**
     * Summary of paystackWebhook
     * @param Request $request
     * @return mixed
     */
    public function paystackWebhook(Request $request)
    {
        try {
            $paystackSecretKey = config('services.paystack.secret_key');
            $calculatedSignature = hash_hmac('sha512', $request->getContent(), $paystackSecretKey);

            if ($calculatedSignature !== $request->header('x-paystack-signature')) {
                throw new \Exception('Invalid webhook signature');
            }

            $event = $request->event;
            $data = $request->data;
            http_response_code(200);
            if ($event == 'charge.success') {
                $user = User::where('email', $data['customer']['email'])->first();
                if (!$user) {
                    throw new \Exception('User not found');
                    exit;
                }

                $deposit_exists = Deposit::where('deposit_reference', $data['reference'])->first();

                if ($deposit_exists) {
                    throw new \Exception('Transaction already processed');
                    exit;
                }


                $amount = $data['amount'] / 100;
                credit_user($user->id, $amount);

                // Update transaction status in your database
                // You might want to create a Transaction model to handle this
                TransactionRecords::updateOrCreate(
                    ['deposit_reference' => $data['reference']],
                    [
                        'user_id' => $user->id,
                        'amount' => $amount,
                        'status' => 'success',
                        'payment_method' => 'paystack',
                        'deposit_method' => $data['channel'],
                        'deposit_meta' => $data
                    ]
                );
            }

            return http_response_code(200);
        } catch (\Exception $e) {
            \Log::channel('deposit-log')->error('Paystack Webhook Error: ' . $e->getMessage());
            return http_response_code($e->getCode());
        }
    }


    public function handleWebhook(Request $request)
    {
        $paystackSignature = $request->header('x-paystack-signature');
        $computedSignature = hash_hmac('sha512', $request->getContent(), $this->paystackSecretKey);

        if ($paystackSignature !== $computedSignature) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $paystack = new PaystackServices();

        $payload = $request->all();
        $event = $payload['event'];

        switch ($event) {
            case 'charge.success':
                $paystack->handleSuccessfulCharge($payload['data']);
                break;
            case 'transfer.success':
                $paystack->handleSuccessfulTransfer($payload['data']);
                break;
            case 'dedicated_account.assign.success':
                $paystack->handleVirtualAccountCreation($payload['data']);
                break;
        }

        return response()->json(['message' => 'Webhook processed']);
    }
}
