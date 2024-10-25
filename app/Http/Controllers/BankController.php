<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BankController extends Controller
{
    public function getBanks(Request $request)
    {
        $url = "https://api.paystack.co/bank";
        $headers = [
            "Authorization: Bearer " . env('PAYSTACK_SECRET_KEY'),
            "Cache-Control: no-cache",
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return get_error_response('Failed to fetch banks', ['error' => 'Failed to fetch banks'], 500);
        }

        $result = json_decode($response, true);
        return get_success_response($result['data'], 'Banks retrieved successfully');
    }

    public function validateAccountNumber(Request $request)
    {
        $request->validate([
            'account_number' => 'required|string|size:10',
            'bank_code' => 'required|string',
        ]);

        $url = "https://api.paystack.co/bank/resolve?account_number=" . $request->account_number . "&bank_code=" . $request->bank_code;
        $headers = [
            "Authorization: Bearer " . env('PAYSTACK_SECRET_KEY'),
            "Cache-Control: no-cache",
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return get_error_response('Failed to validate account', ['error' => 'Failed to validate account'], 500);
        }

        $result = json_decode($response, true);
        return get_success_response($result['data'], "Account number resolved");
    }
}
