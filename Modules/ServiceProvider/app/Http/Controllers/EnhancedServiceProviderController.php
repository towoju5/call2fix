<?php

namespace Modules\ServiceProvider\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Artisans;
use App\Models\Property;
use App\Models\ServiceRequest;
use App\Models\SubmittedQuotes;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Modules\Artisan\Models\ArtisanQuotes;
use Validator;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ServiceProviderActionNotification;

class EnhancedServiceProviderController extends Controller
{
    protected $radiusLimitKm;

    public function __construct()
    {
        $this->radiusLimitKm = get_settings_value('max_provider_radius') ?? 30;
    }

    private function sendNotification($user, $action, $data)
    {
        Notification::send($user, new ServiceProviderActionNotification($action, $data));
    }

    public function artisans()
    {
        $artisans = User::role('artisan')
            ->where('service_provider_id', auth()->id())
            ->with('artisan_quotes')
            ->latest()
            ->get();
        
       
        return get_success_response($artisans, "All artisans retrieved successfully");
    }

    public function viewArtisan($id)
    {
        $artisan = User::where('id', $id)
            ->whereHas('artisans', function ($query) {
                $query->where('service_provider_id', auth()->id());
            })
            ->with('artisan_quotes')
            ->firstOrFail();

        return get_success_response($artisan, "Artisan retrieved successfully");
    }

    public function deleteArtisan($id)
    {
        $artisan = Artisans::where('artisan_id', $id)
            ->where('service_provider_id', auth()->id())
            ->firstOrFail();

        $artisan->delete();

        $this->sendNotification(auth()->user(), 'artisan_deleted', $artisan);
        return get_success_response(null, "Artisan deleted successfully");
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "first_name" => "required|string|max:255",
            "last_name" => "required|string|max:255",
            "email" => "required|email|unique:users,email",
            "phone" => "required|string|max:20",
            "trade" => "required|string|max:255",
            "location" => "required|string|max:255",
            "id_type" => "required|string|in:national_id,drivers_license,passport,voters_card",
            "id_image" => "required|image|mimes:jpeg,png,jpg|max:2048",
            "trade_certificate" => "required|file|mimes:pdf,jpeg,png,jpg|max:2048",
            "payment_plan" => "required|string|in:percentage,fixed",
            "payment_amount" => "required|numeric|min:0",
            "bank_code" => "required|string|max:20",
            "account_number" => "required|string|max:20",
            "account_name" => "required|string|max:255",
            "artisan_category" => "sometimes|exists:categories,id"
        ]);

        if ($validator->fails()) {
            return get_error_response("Validation error", $validator->errors(), 422);
        }

        $validateData = $validator->validated();
        $validateData['account_type'] = "artisan";
        $validateData['service_provider_id'] = auth()->id();

        $artisan = User::create($validateData);

        if ($artisan) {
            $this->sendNotification(auth()->user(), 'artisan_created', $artisan);
            return get_success_response($artisan, "Artisan created successfully", 201);
        }

        return get_error_response("Unable to complete request", ["error" => "Unable to create artisan, please try again"], 500);
    }

    public function artisanQuotes()
    {
        $quotes = ArtisanQuotes::whereServiceProviderId(auth()->id())->latest()->get();
        return get_success_response($quotes, "All quotes retrieved successfully");
    }

    public function submitQuote(Request $request)
    {
        $validate = Validator::make($request->all(), [
            "request_id" => "required|exists:service_requests,id",
            'workmanship' => 'required|numeric',
            'items' => 'array|required',
            'sla_duration' => 'required|string',
            'sla_start_date' => 'required|date',
            'attachments' => 'nullable|array',
            'summary_note' => 'required|string',
        ]);

        if ($validate->fails()) {
            return get_error_response("Validation failed", $validate->errors(), 422);
        }

        if (SubmittedQuotes::where(["provider_id" => auth()->id(), "request_id" => $request->request_id])->exists()) {
            return get_error_response("Duplicate submission", ["error" => "You have already submitted a quote for this request"], 409);
        }

        $createQuote = SubmittedQuotes::create([
            "provider_id" => auth()->id(),
            "request_id" => $request->request_id,
            "workmanship" => $request->workmanship,
            "sla_duration" => $request->sla_duration,
            "sla_start_date" => $request->sla_start_date,
            "attachments" => $request->attachments,
            "summary_note" => $request->summary_note,
            "administrative_fee" => get_settings_value('administrative_fee', 500),
            "service_vat" => ($request->workmanship + get_settings_value('administrative_fee')) * 0.075,
            "items" => $request->items
        ]);

        $this->sendNotification(auth()->user(), 'quote_submitted', $createQuote);
        return get_success_response($createQuote, "Quote submitted successfully", 201);
    }

    public function getQuotes()
    {
        $quotes = SubmittedQuotes::whereProviderId(auth()->id())->latest()->get();
        return get_success_response($quotes, "Service provider quotes retrieved successfully");
    }

    public function getProviders($propertyId)
    {
        $property = Property::findOrFail($propertyId);
        $radiusLimitMeters = $this->radiusLimitKm * 1000;

        $providers = User::role(Controller::SERVICE_PROVIDERS)
            ->select(DB::raw('*, ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) as distance'))
            ->whereRaw('ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?', [
                $property->longitude,
                $property->latitude,
                $property->longitude,
                $property->latitude,
                $radiusLimitMeters
            ])
            ->orderBy('distance')
            ->get();

        return get_success_response($providers, "Nearby service providers retrieved successfully");
    }

    public function getFeaturedProvider($propertyId)
    {
        $property = Property::findOrFail($propertyId);
        $radiusLimitMeters = $this->radiusLimitKm * 1000;

        $featuredProvider = User::role(Controller::SERVICE_PROVIDERS)
            ->select(DB::raw('*, ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) as distance'))
            ->where('is_featured', true)
            ->whereRaw('ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?', [
                $property->longitude,
                $property->latitude,
                $property->longitude,
                $property->latitude,
                $radiusLimitMeters
            ])
            ->orderBy('distance')
            ->first();

        if (!$featuredProvider) {
            return get_error_response("No featured provider", "No featured service provider found nearby", 404);
        }

        return get_success_response($featuredProvider, "Nearby featured service provider retrieved successfully");
    }

    public function getRequests()
    {
        $requests = ServiceRequest::whereJsonContains('featured_providers_id', [auth()->id()])->get();
        return get_success_response($requests, "Service provider requests retrieved successfully");
    }

    public function acceptQuote($quoteId, $requestId)
    {
        $quotes = SubmittedQuotes::whereRequestId($requestId)->get();
        
        if ($quotes->isEmpty()) {
            return get_error_response("Quote not found", ["error" => "Quote not found!"], 404);
        }

        $quotes->each(function ($quote) use ($quoteId) {
            $quote->status = ($quote->id == $quoteId) ? "accepted" : "rejected";
            $quote->save();
        });

        $acceptedQuote = $quotes->firstWhere('id', $quoteId);
        
        if ($acceptedQuote) {
            $this->sendNotification($acceptedQuote->provider, 'quote_accepted', $acceptedQuote);
            return get_success_response($acceptedQuote, "Quote accepted successfully");
        }

        return get_error_response("Failed to save", ["error" => "Failed to save the accepted quote"], 500);
    }

    public function rejectQuote($quoteId, $requestId)
    {
        $quote = SubmittedQuotes::whereRequestId($requestId)->whereId($quoteId)->firstOrFail();

        $quote->status = "rejected";
        
        if ($quote->save()) {
            $this->sendNotification($quote->provider, 'quote_rejected', $quote);
            return get_success_response($quote, "Quote rejected successfully");
        }

        return get_error_response("Failed to save", ["error" => "Failed to save the rejected quote"], 500);
    }
}
