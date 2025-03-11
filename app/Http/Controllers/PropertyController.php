<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Notifications\Property\CreatedNotification;
use App\Notifications\Property\DeletedNotification;
use App\Notifications\Property\UpdatedNotification;
use Illuminate\Http\Request;
use DB;

class PropertyController extends Controller
{
    public function index()
    {
        try {
            $user = auth()->user();
            $properties = Property::whereUserId(auth()->id());
            if(!($user->parent_account_id)) {
                $properties = $properties->orWhere('user_id', $user->parent_account_id)->first();
            }
            return get_success_response($properties->latest()->get(););
        } catch (\Exception $e) {
            return get_error_response($e->getMessage(), ['error' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'property_address' => 'required|string',
                'property_type' => 'required|string',
                'property_nearest_landmark' => 'required|string',
                'property_name' => 'required|string',
                'porperty_longitude' => 'required|string',
                'porperty_latitude' => 'required|string'
            ]);
            $validatedData['user_id'] = auth()->id();
            $property = Property::create($validatedData);
            auth()->user()->notify(new CreatedNotification($property));
            return get_success_response($property, 'Property created successfully');
        } catch (\Exception $e) {
            return get_error_response($e->getMessage(), ['error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {
            $property = DB::table('properties')->where('id', $id)->first();
            return get_success_response($property);
        } catch (\Exception $e) {
            return get_error_response($e->getMessage(), ['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $property = Property::findOrFail($id);

            if(!auth()->user()->id == $property->user_id){
                return get_error_response('Property not found', ['error' => 'Property not found']);
            }

            $validatedData = $request->validate([
                'property_address' => 'string',
                'property_type' => 'string',
                'property_nearest_landmark' => 'string',
                'property_name' => 'string',
                'porperty_longitude' => 'required|string',
                'porperty_latitude' => 'required|string'
            ]);

            $property->update($validatedData);
            auth()->user()->notify(new UpdatedNotification($property));
            return get_success_response($property, 'Property updated successfully');
        } catch (\Exception $e) {
            return get_error_response($e->getMessage(), ['error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $property = Property::findOrFail($id);
            $property->delete();
            auth()->user()->notify(new DeletedNotification($property));
            return get_success_response(null, 'Property deleted successfully');
        } catch (\Exception $e) {
            return get_error_response($e->getMessage(), ['error' => $e->getMessage()]);
        }
    }
}
