<?php

namespace Modules\ServiceProvider\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ArtisanCanSubmitQuote;
use App\Models\Artisans;
use App\Models\Property;
use App\Models\ServiceRequest;
use App\Models\SubmittedQuotes;
use App\Models\User;
use DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Artisan\Models\ArtisanQuotes;
use Modules\ServiceProvider\Models\ServiceLocations;
use Validator;

class ServiceProviderController extends Controller
{
    protected $radiusLimitKm;
    public function __construct()
    {
        $this->radiusLimitKm = get_settings_value('max_provider_radius') ?? 30;
    }

    public function artisans()
    {
        $artisans = User::role('artisan')->where('service_provider_id', auth()->id())
            // ->whereHas('artisan', function ($query) {
            //     $query->where('service_provider_id', auth()->id());
            // })
            ->with('artisan_quotes')
            ->latest()
            ->get();
        return get_success_response($artisans, "All artisans retrieved successfully");
    }

    public function viewArtisan($id)
    {
        $artisan = User::where('id', $id)
            ->whereHas('artisan', function ($query) {
                $query->where('service_provider_id', auth()->id());
            })
            ->with('artisan_quotes')
            ->first();

        if (!$artisan) {
            return get_error_response("Artisan not found", [], 404);
        }

        return get_success_response($artisan, "Artisan retrieved successfully");
    }

    public function deleteArtisan($id)
    {
        $artisan = Artisans::where('artisan_id', $id)
            ->where('service_provider_id', auth()->id())
            ->first();

        if (!$artisan) {
            return get_error_response("Artisan not found", [], 404);
        }

        $artisan->delete();

        return get_success_response(null, "Artisan deleted successfully");
    }

    /**
     * Add new artisan
     * @param \Illuminate\Http\Request $request
     * @return 
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "first_name" => "required|string|max:255",
                "last_name" => "required|string|max:255",
                "email" => "required|email|unique:users,email",
                "phone" => "required|string|max:20",
                "trade" => "required|string|max:255", // category the artisan is registered under - max.
                "location" => "required|string|max:255", // locations service provider offers services.
                "id_type" => "required|string|in:national_id,drivers_license,passport,voters_card",
                "id_image" => "required",
                "trade_certificate" => "required",
                "payment_plan" => "required|string|in:percentage,fixed",
                "payment_amount" => "required|numeric|min:0",
                "bank_code" => "required|string|max:20",
                "account_number" => "required|string|max:20",
                "account_name" => "required|string|max:255",
                "artisan_category" => "sometimes|exists:categories,id"
            ]);

            if ($validator->fails()) {
                return get_error_response("Validation error", $validator->errors()->toArray(), 422);
            }
            $validateData = $validator->validated();
            $validateData['account_type'] = "artisan";
            $validateData['service_provider_id'] = auth()->id();

            if (Artisans::firstOrCreate($validateData)) {
                return get_success_response($validateData, "Artisan created successfully", 200);
            }

            return get_error_response("Unable to complete request", ["error" => "Unable to complete request, please contact support if error persists"], 400);
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), [], $th->getCode());
        }
    }

    public function artisanQuotes()
    {
        try {
            $quotes = ArtisanQuotes::whereServiceProviderId(auth()->id())->latest()->get();
            return get_success_response($quotes, "All quotes retrieved successfully");
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()]);
        }
    }

    public function submitQuote(Request $request)
    {
        try {
            $validate = Validator::make(request()->all(), [
                "request_id" => "required",
                'workmanship' => 'required',
                'items' => 'array|required',
                'sla_duration' => 'required',
                'sla_start_date' => 'required',
                'attachments' => 'nullable',
                'summary_note' => 'required',
            ]);

            if ($validate->fails()) {
                return get_error_response("Validation failed", $validate->errors());
            }

            // Check if quote already submitted
            if (SubmittedQuotes::where(["provider_id" => auth()->id(), "request_id" => $request->request_id])->exists()) {
                return get_error_response("You have already submitted a quote for this request", ["error" => "You have already submitted a quote for this request"]);
            }

            // Process quote submission 
            $createQuote = SubmittedQuotes::firstOrCreate(
                [
                    "provider_id" => auth()->id(),
                    "request_id" => $request->request_id,
                ],
                [
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
                ]
            );

            // $createQuote->items()->save($request->items);

            return get_success_response($createQuote, "Quote submitted successfully");

        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), [], 500);
        }
    }

    /**
     * Return quotes by service providers from 
     * model: SubmittedQuotes
     * 
     * @return mixed
     */
    public function getQuotes()
    {
        try {
            $quotes = SubmittedQuotes::where("provider_id", auth()->id())->latest()->get();
            return get_success_response($quotes, "Service provider quotes retrieved successfully");
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()]);
        }
    }

    public function getProviders($propertyId)
    {
        try {
            // Get the property details
            $property = Property::findOrFail($propertyId);

            // Get the authenticated user
            $user = auth()->user();

            // Get the radius limit in kilometers from settings and convert to meters
            $radiusLimitKm = get_settings_value('max_provider_radius');
            $radiusLimitMeters = $radiusLimitKm * 1000; // Convert km to meters

            // Get service providers within the radius limit
            $providers = User::role(Controller::SERVICE_PROVIDERS)->select(DB::raw('*, ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) as distance'))
                ->whereRaw('ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?', [
                    $property->longitude,
                    $property->latitude,
                    $radiusLimitMeters
                ])
                ->orderBy('distance')
                ->get();

            return get_success_response($providers, "Nearby service providers retrieved successfully");
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), [], 500);
        }
    }

    public function getFeaturedProvider($propertyId)
    {
        try {
            // Get the property details
            $property = Property::findOrFail($propertyId);

            // Get the authenticated user
            $user = auth()->user();

            // Get the radius limit in kilometers from settings and convert to meters
            $radiusLimitMeters = $this->radiusLimitKm * 1000; // Convert km to meters

            // Get the closest featured service provider within the radius limit
            $featuredProvider = User::role(Controller::SERVICE_PROVIDERS)->removeRoleselect(DB::raw('*, ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) as distance'))
                ->where('is_featured', true)
                ->whereRaw('ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?', [
                    $property->longitude,
                    $property->latitude,
                    $radiusLimitMeters
                ])
                ->orderBy('distance')
                ->first();

            if (!$featuredProvider) {
                return get_error_response("No featured service provider found nearby", [], 404);
            }

            return get_success_response($featuredProvider, "Nearby featured service provider retrieved successfully");
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), [], 500);
        }
    }

    public function getRequests()
    {
        try {
            $requests = ServiceRequest::whereJsonContains('featured_providers_id', [auth()->id()])->get();
            return get_success_response($requests, "Service provider requests retrieved successfully");
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()]);
        }
    }

    public function addServiceLocations(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                "address" => "required|string",
                "latitude" => "required|string",
                "longitude" => "required|string"
            ]);

            if ($validate->fails()) {
                return get_error_response("Validation Error", ['error' => $validate->errors()->toArray()]);
            }

            $location = new ServiceLocations();
            $location->address = $request->address;
            $location->latitude = $request->latitude;
            $location->longitude = $request->longitude;
            $location->user_id = auth()->id();

            if ($location->save()) {
                return get_success_response($location, "Service location added successfully");
            }
            return get_error_response("Unable to add Service location", ["error" => "Unable to add service location"]);
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()]);
        }
    }

    public function getServiceLocations(Request $request)
    {
        try {
            $location = ServiceLocations::where('user_id', auth()->id())->get();
            if ($location) {
                return get_success_response($location, "Service location retrieved successfully");
            }
            return get_error_response("Unable to retrieved Service location", ["error" => "Unable to retrieved service location"]);
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()]);
        }
    }

    public function addArtisanToRequest(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                "request_id" => "required|exists:service_requests,id",
                "artisan_id" => "required|exists:users,id",
                "provider_id" => "required|exists:users,id"
            ]);


            if ($validate->fails()) {
                return get_error_response("Validation Error", $validate->errors()->toArray());
            }

            if ($artisan = ArtisanCanSubmitQuote::create($validate->validated())) {
                return get_success_response($artisan, "Artisan invited successfully");
            }

            return get_error_response("Error encountered", ["error" => "Error encountered, please contact support."]);
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()]);
        }
    }
}
