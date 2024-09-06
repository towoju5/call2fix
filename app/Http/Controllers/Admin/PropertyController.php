<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->can('view properties')) {
            return back()->with('error', 'Unauthorized action.');
        }

        try {
            $properties = Property::with('user', 'order')->get();
            return get_success_response(['properties' => $properties], 'Properties fetched successfully.', 200);
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while fetching properties.');
        }
    }

    public function create(Request $request)
    {
        if (!$request->user()->can('create properties')) {
            return back()->with('error', 'Unauthorized action.');
        }

        try {
            $users = User::all();
            return view('admin.properties.create', compact('users'));
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while loading the create form.');
        }
    }

    public function store(Request $request)
    {
        if (!$request->user()->can('create properties')) {
            return back()->with('error', 'Unauthorized action.');
        }

        try {
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',
                'property_address' => 'required|string|max:255',
                'property_type' => 'required|string|max:100',
                'property_nearest_landmark' => 'required|string|max:255',
                'property_name' => 'required|string|max:255',
                'porperty_longitude' => 'required|string',
                'porperty_latitude' => 'required|string'
            ]);

            $property = Property::create($validatedData);

            return get_success_response(['property' => $property], 'Property created successfully.', 201);
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while creating the property.');
        }
    }

    public function show(Request $request, Property $property)
    {
        if (!$request->user()->can('view properties')) {
            return back()->with('error', 'Unauthorized action.');
        }

        try {
            return get_success_response(['property' => $property->load('user')], 'Property retrieved successfully.', 200);
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while retrieving the property.');
        }
    }

    public function edit(Request $request, Property $property)
    {
        if (!$request->user()->can('edit properties')) {
            return back()->with('error', 'Unauthorized action.');
        }

        try {
            $users = User::all();
            return view('admin.properties.edit', compact('property', 'users'));
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while loading the edit form.');
        }
    }

    public function update(Request $request, Property $property)
    {
        if (!$request->user()->can('edit properties')) {
            return back()->with('error', 'Unauthorized action.');
        }

        try {
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',
                'property_address' => 'required|string|max:255',
                'property_type' => 'required|string|max:100',
                'property_nearest_landmark' => 'required|string|max:255',
                'property_name' => 'required|string|max:255',
                'porperty_longitude' => 'required|string',
                'porperty_latitude' => 'required|string'
            ]);

            $property->update($validatedData);

            return get_success_response(['property' => $property], 'Property updated successfully.', 200);
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while updating the property.');
        }
    }

    public function destroy(Request $request, Property $property)
    {
        if (!$request->user()->can('delete properties')) {
            return back()->with('error', 'Unauthorized action.');
        }

        try {
            $property->delete();
            return get_success_response([], 'Property deleted successfully.', 200);
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while deleting the property.');
        }
    }
}
