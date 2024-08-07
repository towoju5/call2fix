<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index()
    {
        try {
            $properties = auth()->user()->properties();
            return get_success_response($properties);
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
            ]);
            $validatedData['user_id'] = auth()->id();
            $property = Property::create($validatedData);
            return get_success_response($property, 'Property created successfully');
        } catch (\Exception $e) {
            return get_error_response($e->getMessage(), ['error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {
            $property = Property::findOrFail($id);
            return get_success_response($property);
        } catch (\Exception $e) {
            return get_error_response($e->getMessage(), ['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $property = Property::findOrFail($id);

            $validatedData = $request->validate([
                'user_id' => 'exists:users,id',
                'property_address' => 'string',
                'property_type' => 'string',
                'property_nearest_landmark' => 'string',
                'property_name' => 'string',
            ]);

            $property->update($validatedData);
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
            return get_success_response(null, 'Property deleted successfully');
        } catch (\Exception $e) {
            return get_error_response($e->getMessage(), ['error' => $e->getMessage()]);
        }
    }
}
