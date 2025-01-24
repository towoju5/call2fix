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
        try {
            $query = Property::with('user')->withTrashed();

            // Filter for trashed items
            if ($request->filled('status')) {
                if ($request->status === 'trashed') {
                    $query->onlyTrashed();
                } elseif ($request->status === 'active') {
                    $query->whereNull('deleted_at');
                }
            }

            // Search by user_id
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // Filter by account type
            if ($request->filled('_account_type')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('account_type', $request->_account_type);
                });
            }

            // Sorting
            if ($request->filled('sort_by') && $request->filled('sort_order')) {
                $query->orderBy($request->sort_by, $request->sort_order);
            }

            $properties = $query->paginate(10);

            return view('admin.properties.index', compact('properties'));
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while fetching properties.');
        }
    }

    public function create()
    {
        $users = User::all();
        return view('admin.properties.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'property_address' => 'required|string|max:255',
            'property_type' => 'required|string|max:100',
            'property_nearest_landmark' => 'required|string|max:255',
            'property_name' => 'required|string|max:255',
            'porperty_longitude' => 'required|string',
            'porperty_latitude' => 'required|string',
        ]);

        try {
            $property = Property::create($validatedData);
            return redirect()->route('admin.properties.index')->with('success', 'Property created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while creating the property.');
        }
    }

    public function show(Property $property)
    {
        return view('admin.properties.show', compact('property'));
    }

    public function edit(Property $property)
    {
        $users = User::all();
        return view('admin.properties.edit', compact('property', 'users'));
    }

    public function update(Request $request, Property $property)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'property_address' => 'required|string|max:255',
            'property_type' => 'required|string|max:100',
            'property_nearest_landmark' => 'required|string|max:255',
            'property_name' => 'required|string|max:255',
            'porperty_longitude' => 'required|string',
            'porperty_latitude' => 'required|string',
        ]);

        try {
            $property->update($validatedData);
            return redirect()->route('admin.properties.index')->with('success', 'Property updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while updating the property.');
        }
    }

    public function destroy(Property $property)
    {
        try {
            $property->delete();
            return redirect()->route('admin.properties.index')->with('success', 'Property trashed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while deleting the property.');
        }
    }

    public function trashed()
    {
        try {
            $properties = Property::onlyTrashed()->with('user')->paginate(10);
            return view('admin.properties.trashed', compact('properties'));
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while fetching trashed properties.');
        }
    }

    public function restore($id)
    {
        try {
            $property = Property::onlyTrashed()->findOrFail($id);
            $property->restore();

            return redirect()->route('admin.properties.index')->with('success', 'Property restored successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while restoring the property.');
        }
    }

    public function forceDelete($id)
    {
        try {
            $property = Property::onlyTrashed()->findOrFail($id);
            $property->forceDelete();

            return redirect()->route('admin.properties.index')->with('success', 'Property permanently deleted.');
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while permanently deleting the property.');
        }
    }
}
