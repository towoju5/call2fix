<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServicesController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->can('view services')) {
            return back()->with('error', 'Unauthorized action.');
        }

        try {
            $services = Service::with('category')->get();
            return back()->with('success', $services);
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while fetching services.');
        }
    }

    public function create(Request $request)
    {
        if (!$request->user()->can('create services')) {
            return back()->with('error', 'Unauthorized action.');
        }

        try {
            $categories = Category::all();
            return view('admin.services.create', compact('categories'));
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while loading the create form.');
        }
    }

    public function store(Request $request)
    {
        if (!$request->user()->can('create services')) {
            return back()->with('error', 'Unauthorized action.');
        }

        try {
            $validatedData = $request->validate([
                'category_id' => 'required|exists:categories,id',
                'service_name' => 'required|string|max:255',
                'metadata' => 'nullable|json',
            ]);

            $validatedData['service_slug'] = Str::slug($validatedData['service_name']);

            $service = Service::create($validatedData);

            return back()->with('success', $service);
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while creating the service.');
        }
    }

    public function show(Request $request, Service $service)
    {
        if (!$request->user()->can('view services')) {
            return back()->with('error', 'Unauthorized action.');
        }

        try {
            return back()->with('success', $service->load('category'));
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while retrieving the service.');
        }
    }

    public function edit(Request $request, Service $service)
    {
        if (!$request->user()->can('edit services')) {
            return back()->with('error', 'Unauthorized action.');
        }

        try {
            $categories = Category::all();
            return view('admin.services.edit', compact('service', 'categories'));
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while loading the edit form.');
        }
    }

    public function update(Request $request, Service $service)
    {
        if (!$request->user()->can('edit services')) {
            return back()->with('error', 'Unauthorized action.');
        }

        try {
            $validatedData = $request->validate([
                'category_id' => 'required|exists:categories,id',
                'service_name' => 'required|string|max:255',
                'metadata' => 'nullable|json',
            ]);

            $validatedData['service_slug'] = Str::slug($validatedData['service_name']);

            $service->update($validatedData);

            return back()->with('success', $service);
        } catch (\Exception $e) { 
            return back()->with('error', 'An error occurred while updating the service.');
        }
    }

    public function destroy(Request $request, Service $service)
    {
        if (!$request->user()->can('delete services')) {
            return back()->with('error', 'Unauthorized action.');
        }

        try {
            $service->delete();
            return back()->with('success', 'Service deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while deleting the service.');
        }
    }
}
