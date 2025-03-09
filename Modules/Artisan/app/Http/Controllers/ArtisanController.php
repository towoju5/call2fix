<?php

namespace Modules\Artisan\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ArtisanCanSubmitQuote;
use App\Models\ItemRequest;
use App\Models\Artisans as Artisan;
use App\Models\Order;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Artisan\Models\ArtisanQuotes;
use Validator;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


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
            $requests = ServiceRequestModel::whereIn('id', $requestIds)->with([
                'user',
                'service_provider',
                'artisan',
                'property',
                'serviceCategory',
                'service',
                'reworkMessages',
                'checkIns',
            ])->latest()->get();
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
                // "service_provider_id" => "required",
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
            
            $artisan = Artisan::where('artisan_id', auth()->id())->first();
            if(!$artisan) {
                return get_error_response("Service provider not found", ["error" => "Service provider not found"]);
            }
            
            $request->merge([
                "service_provider_id" => $artisan->service_provider_id
            ]);

            if (ArtisanCanSubmitQuote::where(["artisan_id" => auth()->id(), "request_id" => $request->request_id])->exists()) {
                // Check if quote already submitted
                if (ArtisanQuotes::where(["artisan_id" => auth()->id(), "request_id" => $request->request_id])->exists()) {
                    return get_error_response("You have already submitted a quote for this request", ["error" => "You have already submitted a quote for this request"]);
                }

                $items_total = 0;

                // Calculate total for items
                if ($request->has('items') && !empty($request->items)) {
                    $items = $request->items;
                    foreach ($items as $item) {
                        $items_total += $item['quantity'] * $item['price'];
                    }
                }
                
                // Calculate total fee (excluding VAT)
                $total_fee = $request->workmanship + $items_total + get_settings_value('administrative_fee');
                
                // Calculate VAT (on total fee)
                $service_vat = $total_fee * 0.075;
                
                // Calculate final total charges (total fee + VAT)
                $total_charges = $total_fee + $service_vat;
                
            
                if (!Schema::hasColumn('artisan_quotes', 'old_price')) {
                    Schema::table('artisan_quotes', function (Blueprint $table) {
                        $table->string('old_price')->nullable();
                    });
                }

                // Process quote submission 
                $createQuote = ArtisanQuotes::updateOrCreate(
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
                        "sla_start_date" => Carbon::parse($request->sla_start_date)->format('Y-m-d'),  // Format to date (Y-m-d)
                        "attachments" => $request->attachments,
                        "summary_note" => $request->summary_note,
                        "administrative_fee" => get_settings_value('administrative_fee'),
                        "service_vat" => $service_vat,
                        "items" => $request->items,
                        "old_price" => $request->total_charges ?? $total_charges,
                        "total_charges" => $request->total_charges ?? $total_charges,
                    ]
                );


                // $createQuote->items()->save($request->items);

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
