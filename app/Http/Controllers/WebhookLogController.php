<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebhookLogController extends Controller
{
    /**
     * Summary of paystackWebhook
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
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
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            \Log::channel('deposit-log')->error('Paystack Webhook Error: ' . $e->getMessage());
            return http_response_code(0);
            return response()->json(['status' => 'error'], 500);
        }
    }
}
