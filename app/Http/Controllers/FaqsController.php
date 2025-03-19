<?php

namespace App\Http\Controllers;

use App\Mail\SupportEmail;
use App\Models\Faqs;
use Illuminate\Http\Request;
use Mail;
use Validator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FaqsController extends Controller
{
    public function __construct()
    {
        if(!Schema::hasColumn('faqs', 'account_type')) {
            Schema::table('faqs', function(Blueprint $table) {
                $table->string('account_type')->nullable();
            });
        }
    }

    public function index()
    {
        $faqs = Faqs::where('account_type', active_role)->get();
        return get_success_response($faqs);
    }

    public function show(Faqs $faq)
    {
        return get_success_response($faq);
    }

    public function sendSupportEmail(Request $request)
    {
        $supportEmail = get_settings_value('support_email');

        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'account_type' => 'required',
            'message' => 'required|string'
        ]);

        if ($validator->fails()) {
            return get_error_response("Validation error", $validator->errors()->toArray(), 422);
        }

        try {
            $emailData = $validator->validated();
            $emailData['sender'] = auth()->user();

            $send = Mail::to($supportEmail)->send(new SupportEmail($emailData));
            if ($send) {
                return get_success_response(['message' => 'Email sent successfully'], 'Email sent successfully');
            }

            return get_error_response('Failed to send email');
        } catch (\Exception $e) {
            return get_error_response('Failed to send email: ' . $e->getMessage(), ['error' => $e->getMessage()]);
        }
    }

}
