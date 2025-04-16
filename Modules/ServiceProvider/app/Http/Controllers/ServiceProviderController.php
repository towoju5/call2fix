<?php

namespace Modules\ServiceProvider\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ArtisanCanSubmitQuote;
use App\Models\Artisans;
use App\Models\Property;
use App\Models\ServiceRequest;
use App\Models\SubmittedQuotes;
use App\Models\User;
use DB, Str;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Artisan\Models\ArtisanQuotes;
use Modules\ServiceProvider\Models\ServiceLocations;
use Validator;
use App\Notifications\NewArtisanAddedNotification;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Notifications\CustomNotification;


class ServiceProviderController extends Controller
{
    protected $radiusLimitKm;
    public function __construct()
    {
        $this->radiusLimitKm = get_settings_value('max_provider_radius') ?? 30;
    }

    public function artisans()
    {
        $artisans = Artisans::where('service_provider_id', auth()->id())
            ->with('user')
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
    public function addArtisan(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "first_name" => "required|string|max:255",
                "last_name" => "required|string|max:255",
                "email" => "required|email|unique:users,email",
                "phone" => "required|string|max:20|unique:artisans,phone",
                "trade" => "required|string|max:255", // category the artisan is registered under - max.
                "location" => "required|array|max:255", // locations service provider offers services.
                "id_type" => "required|string|in:national_id,drivers_license,passport,voters_card",
                "id_image" => "required",
                "trade_certificate" => "required",
                "payment_plan" => "required|string|in:percentage,fixed",
                "payment_amount" => "required|numeric|min:1",
                "bank_code" => "required|string|max:20",
                "account_number" => "required|string|max:20",
                "account_name" => "required|string|max:255",
                "artisan_category" => "sometimes|exists:categories,id"
            ]);

            if ($validator->fails()) {
                return get_error_response("Validation error", $validator->errors()->toArray(), 422);
            }

            $validateData = $validator->validated();
            $validateData['user_id'] = auth()->id();
            $validateData['service_provider_id'] = auth()->id();

            DB::beginTransaction(); // Start transaction


            if ($request->has('phone')) {
                $userData['phone'] = str_replace(" ", "", $request->phone);
            }

            $user = auth()->user();
            $officeAddresses = Artisans::where(['artisan_category' => $request->artisan_category, 'user_id' => $user->id])->count();
            $subscription = $user->activeSubscription();
            $allowedOfficeAddresses = $subscription->getRemainingOf('artisans');
            if($officeAddresses > $allowedOfficeAddresses) {
                return get_error_response('Feature limit reached', ['error' => 'Feature limit reached'], 403);
            }

            // Add Artisan to user DB
            $artisanPassword = "!".Str::random(8);
            $userData = [
                'first_name' => $validateData['first_name'],
                'last_name' => $validateData['last_name'],
                'email' => $validateData['email'],
                'phone' => $validateData['phone'],
                'password' => bcrypt($artisanPassword),
                'username' => $validateData['first_name'] . rand(23, 999),
                'is_social' => false,
                'account_type' => 'artisan',
                'device_id' => 'device_id',
                'current_role' => 'artisan',
                'main_account_role' => 'artisan',
            ];

            $newArtisan = User::updateOrCreate([
                'email' => $validateData['email'],
                'phone' => $validateData['phone']
            ], $userData);
            if ($newArtisan) {
                $newArtisan->assignRole('artisan');
                $validateData['artisan_id'] = $newArtisan->id;
                Artisans::updateOrCreate($validateData);

                // Send a notification to the artisan with the default password
                $newArtisan->notify(new NewArtisanAddedNotification($newArtisan, $artisanPassword));

                DB::commit(); // Commit transaction if everything is successful
                return get_success_response($validateData, "Artisan created successfully", 200);
            }

            DB::rollBack(); // Roll back if the new artisan could not be created
            return get_error_response("Unable to complete request", ["error" => "Unable to complete request, please contact support if error persists"], 400);

        } catch (\Throwable $th) {
            DB::rollBack(); // Roll back on any exception
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }

    public function updateArtisan(Request $request, $artisanId)
    {
        try {
            $validator = Validator::make($request->all(), [
                "first_name" => "sometimes|string|max:255",
                "last_name" => "sometimes|string|max:255",
                "trade" => "sometimes|string|max:255", // category the artisan is registered under - max.
                "location" => "sometimes|array|max:255", // locations service provider offers services.
                "id_type" => "sometimes|string|in:national_id,drivers_license,passport,voters_card",
                "id_image" => "sometimes",
                "trade_certificate" => "sometimes",
                "payment_plan" => "sometimes|string|in:percentage,fixed",
                "payment_amount" => "sometimes|numeric|min:0",
                "bank_code" => "sometimes|string|max:20",
                "account_number" => "sometimes|string|max:20",
                "account_name" => "sometimes|string|max:255",
                "artisan_category" => "sometimes|exists:categories,id"
            ]);

            if ($validator->fails()) {
                return get_error_response("Validation error", $validator->errors()->toArray(), 422);
            }

            $validateData = $validator->validated();
            $validateData['service_provider_id'] = auth()->id();

            DB::beginTransaction(); // Start transaction


            if ($request->has('phone')) {
                $userData['phone'] = str_replace(" ", "", $request->phone);
            }
            
            // Add Artisan to user DB
            $artisanPassword = Str::random(8);
            $userData = [
                'first_name' => $validateData['first_name'],
                'last_name' => $validateData['last_name'],
                'password' => bcrypt($artisanPassword),
                'username' => $validateData['first_name'] . rand(23, 999),
                'is_social' => false,
                'account_type' => 'artisan',
                'device_id' => 'device_id',
                'current_role' => 'artisan',
                'main_account_role' => 'artisan',
            ];

            $newArtisan = User::whereId($artisanId)->first();
            if(!$newArtisan) {
                return get_error_response("Artisan not found", [], 404);
            }
            
            if ($newArtisan->update($userData)) {
                $newArtisan->assignRole('artisan');
                $validateData['artisan_id'] = $newArtisan->id;
                Artisans::updateOrCreate($validateData);

                // Send a notification to the artisan with the default password
                $newArtisan->notify(new NewArtisanAddedNotification($newArtisan, $artisanPassword));

                DB::commit(); // Commit transaction if everything is successful
                return get_success_response($validateData, "Artisan created successfully", 200);
            }

            DB::rollBack(); // Roll back if the new artisan could not be created
            return get_error_response("Unable to complete request", ["error" => "Unable to complete request, please contact support if error persists"], 400);

        } catch (\Throwable $th) {
            DB::rollBack(); // Roll back on any exception
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }

    public function provider_address(Request $request)
    {
        try {
            $user = auth()->user();
    
            // Ensure the user has a business_info relationship
            $office_address = $user->business_office_address();
            if (!$office_address) {
                return get_error_response("Business information not found.");
            }
            return get_success_response(['locations' => $office_address], "Addresses retrieved successfully.");
        } catch (\Exception $e) {
            return get_error_response("An error occurred while retrieving office addresses.", ['error' => $e->getMessage()]);
        }
    }

    public function providers_category(Request $request)
    {
        try {
            $user = auth()->user();
    
            // Ensure the user has a business_info relationship
            $businessInfo = $user->business_info;
            if (!$businessInfo) {
                return get_error_response("Business information not found.");
            }
    
            // Decode the businessCategory JSON field
            $businessCategoryIds = $businessInfo->businessCategory;
    
            // Ensure businessCategoryIds is an array
            if (!is_array($businessCategoryIds) || empty($businessCategoryIds)) {
                return get_error_response("No business categories found.");
            }
    
            // Fetch categories from the database
            $categories = Category::whereIn('id', $businessCategoryIds)->get();
    
            return get_success_response(['categories' => $categories], "Categories retrieved successfully.");
        } catch (\Exception $e) {
            return get_error_response("An error occurred while retrieving categories.", ['error' => $e->getMessage()]);
        }
    }

    public function artisanQuotes()
    {
        try {
            $quotes = ArtisanQuotes::where('service_provider_id', auth()->id())->latest()->get();
            return get_success_response($quotes, "All quotes retrieved successfully");
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()]);
        }
    }

    public function artisanQuote($requestId)
    {
        try {
            $quotes = ArtisanQuotes::where(['service_provider_id' => auth()->id(), 'request_id' => $requestId])->first();
            return get_success_response($quotes, "Quote retrieved successfully");
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()]);
        }
    }

    /**
     * Summary of submit Quote for Service Requests
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
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
                'total_charges' => 'required',
                'service_vat' => 'required',
            ]);

            if ($validate->fails()) {
                return get_error_response("Validation failed", $validate->errors());
            }

            // Check if quote already submitted    
            if (SubmittedQuotes::where(["provider_id" => auth()->id(), "request_id" => $request->request_id])->exists()) {
                return get_error_response("You have already submitted a quote for this request", ["error" => "You have already submitted a quote for this request"]);
            }

            $service_vat = ($request->workmanship + get_settings_value('administrative_fee')) * 0.075;

            $items_total = 0;
            if ($request->has('items') && !empty($request->items)) {
                $items = $request->items;
                foreach ($items as $item) {
                    $items_total += $item['quantity'] * $item['price'];
                }
                $service_vat += $items_total * 0.075;
            }
              
            // Process quote submission 
            // $items_total = $request->total_charges ?? $items_total;
            $createQuote = SubmittedQuotes::updateOrCreate(
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
                    "administrative_fee" => $request->administrative_fee, //get_settings_value('administrative_fee', 500),
                    "service_vat" => $request->service_vat,
                    "items" => $request->items,
                    "old_price" => $request->total_charges, // ?? $request->workmanship + $items_total + get_settings_value('administrative_fee') + $service_vat,
                    "total_charges" => $request->total_charges, // ?? $request->workmanship + $items_total + get_settings_value('administrative_fee') + $service_vat,
                ]
            );

            // $createQuote->items()->save($request->items);
            // get customer who created service request
            $serviceRequest = ServiceRequest::whereId($request->request_id)->first();
            if($serviceRequest) {
                $customer = User::find($serviceRequest->user_id);
                $customer->notify(new CustomNotification("Quote submitted by provider", "Quote submitted by provider."));
            }
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
            $requests = ServiceRequest::whereJsonContains('featured_providers_id', [auth()->id()])
                        ->orWhere('approved_providers_id', auth()->id())
                        ->paginate(get_settings_value('per_page', 10));
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
                "service_provider_id" => "required|exists:users,id"
            ]);

            $checkExists =  ArtisanCanSubmitQuote::where([
                "request_id" => $request->request_id,
                "artisan_id" => $request->artisan_id,
            ])->exists();

            if($checkExists) {
                return get_error_response("An Artisan has already been added to this project", ['error' => "An Artisan has already been added to this project"]);
            }

            if ($validate->fails()) {
                return get_error_response("Validation Error", $validate->errors()->toArray());
            }

            if ($artisan = ArtisanCanSubmitQuote::create($validate->validated())) {
                // $service_request = ServiceRequest::whereId($request->request_id)->first();
                // if($service_request) {
                //     $service_request->update([
                //         "approved_artisan_id" => $request->artisan_id
                //     ]);
                // }
                return get_success_response($artisan, "Artisan invited successfully");
            }

            return get_error_response("Error encountered", ["error" => "Error encountered, please contact support."]);
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()]);
        }
    }
}
