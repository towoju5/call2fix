<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use App\Models\ServiceArea;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        // if (!$request->user()->can('view categories')) {
        //     return get_error_response('Unauthorized action', ['error' => 'Unauthorized action'], 403);
        // }

        try {
            $categories = Category::with('services')->get();
            return get_success_response($categories, 'Categories fetched successfully.');
        } catch (\Exception $e) {
            return get_error_response('An error occurred while fetching categories.', ['error' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request, $categoryId)
    {
        // if (!$request->user()->can('view categories')) {
        //     return get_error_response('Unauthorized action', ['error' => 'Unauthorized action'], 403);
        // }

        $category = Category::with('services')->where('id', $categoryId)->first();
        
        if (!$category) {
            return get_error_response('Service not found', ['error' => 'Service not found'], 404);
        }

        try {
            return get_success_response($category, 'Category fetched successfully.');
        } catch (\Exception $e) {
            return get_error_response('An error occurred while showing the category', ['error', 'An error occurred while showing the category.']);
        }
    }

    public function service(Request $request, $service)
    {
        // if (!$request->user()->can('view service')) {
        //     return get_error_response('Unauthorized action', ['error' => 'Unauthorized action'], 403);
        // }

        $service = Service::where('category_id', $service)->orWhere('category_id', $service)->get();

        if (!$service) {
            return get_error_response('Service not found', ['error' => 'Service not found'], 404);
        }

        return get_success_response($service, 'Service fetched successfully.');
    }

    public function service_areas()
    {
        return get_success_response(ServiceArea::all()->toArray(), 'Service areas fetched successfully.');
    }
}
