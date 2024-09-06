<?php

namespace Modules\Artisan\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ArtisanCanSubmitQuote;
use App\Models\ItemRequest;
use App\Models\Order;
use App\Models\ServiceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Artisan\Models\ArtisanQuotes;
use Validator;

class ArtisanController extends Controller
{
    public function index()
    {
        try {
            $orders = Order::whereArtisanId(auth()->id())->latest()->get();
            return get_success_response($orders, "All tasks retrieved successfully");
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()]);
        }
    }

    public function requests()
    {
        try {
            $requestIds = ArtisanCanSubmitQuote::whereArtisanId(auth()->id())->latest()->pluck("request_id");
            $requests = ServiceRequest::whereIn('id', $requestIds)->with('user', 'featuredProviders')->latest()->get();
            if ($requests->count() > 0) {
                return get_success_response($requests, "All requests retrieved successfully");
            } else {
                return get_error_response("No requests found", []);
            }
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()]);
        }
    }


    public function submitQuote(Request $request)
    {
        try {
            $validate = Validator::make(request()->all(), [
                "request_id" => "required",
                "service_provider_id" => "required",
                'workmanship' => 'required',
                'items' => 'array|required',
                'sla_duration' => 'required',
                'sla_start_date' => 'required',
                'attachments' => 'nullable',
                'summary_note' => 'required',
                // 'service_vat' => 'required'
            ]);

            if ($validate->fails()) {
                return get_error_response("Validation failed", $validate->errors());
            }

            if (ArtisanCanSubmitQuote::where(["artisan_id" => auth()->id(), "request_id" => $request->request_id])->exists()) {
                // Check if quote already submitted
                if (ArtisanQuotes::where(["artisan_id" => auth()->id(), "request_id" => $request->request_id])->exists()) {
                    return get_error_response("You have already submitted a quote for this request", ["error" => "You have already submitted a quote for this request"]);
                }

                // Process quote submission 
                $createQuote = ArtisanQuotes::firstOrCreate(
                    [
                        "artisan_id" => auth()->id(),
                        "request_id" => $request->request_id,
                        "service_provider_id" => $request->service_provider_id
                    ],
                    [
                        "artisan_id" => auth()->id(),
                        "request_id" => $request->request_id,
                        "service_provider_id" => $request->service_provider_id,
                        "workmanship" => $request->workmanship,
                        "sla_duration" => $request->sla_duration,
                        "sla_start_date" => $request->sla_start_date,
                        "attachments" => $request->attachments,
                        "summary_note" => $request->summary_note,
                        "administrative_fee" => get_settings_value('administrative_fee'),
                        "service_vat" => ($request->workmanship + get_settings_value('administrative_fee')) * 0.075
                    ]
                );

                $createQuote->items()->save($request->items);

                return get_success_response($createQuote, "Quote submitted successfully");
            } else {
                return get_error_response("You are not allowed to submit a quote for this request", ["error" => "Not authorized to submit quote"]);
            }
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), [], 500);
        }
    }

    /**
     * Retrieve all quotes by Artisan
     */
    public function quotes()
    {
        try {
            $quotes = ArtisanQuotes::whereArtisanId(auth()->id())->with('request.user')->latest()->get();
            if ($quotes->count() > 0) {
                return get_success_response($quotes, "All quotes retrieved successfully");
            } else {
                return get_error_response("No quotes found", ["error" => "No quotes found"]);
            }
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()]);
        }
    }

    public function updateQuoteStatus(Request $request, $quoteId)
    {
        try {
            $validate = Validator::make(request()->all(), [
                "status" => "required",
            ]);
            if ($validate->fails()) {
                return get_error_response("Validation failed", $validate->errors());
            }
            $quote = ArtisanQuotes::where('artisan_id', auth()->id())->orWhere('service_provider_id', auth()->id())->whereId($quoteId)->first();
            if ($quote) {
                $quote->request_status = $request->status;
                $quote->save();
                return get_success_response($quote, "Quote status updated successfully");
            } else {
                return get_error_response("Quote not found", ["error" => "Quote not found"]);
            }
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()]);
        }
    }

}
