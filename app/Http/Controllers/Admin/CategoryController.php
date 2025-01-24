<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ServiceArea;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('serviceArea')->paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $serviceAreas = ServiceArea::all();
        return view('admin.categories.create', compact('serviceAreas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:255',
            'category_slug' => 'required|string|unique:categories,category_slug|max:255',
            'parent_category' => 'nullable|exists:service_areas,id', // Allow null if not mandatory
            'category_description' => 'nullable|string',
            'category_image' => 'nullable|image|max:2048'
        ]);
    
        $category = new Category();
        $category->id = Str::uuid(); 
        $category->category_name = $request->category_name;
        $category->category_slug = $request->category_slug;
        $category->parent_category = $request->parent_category;
        $category->category_description = $request->category_description;
    
        if ($request->hasFile('category_image')) {
            $category->category_image = $request->file('category_image')->store('category_images', 'public');
        }
    
        $category->save();
    
        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }


    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $serviceAreas = ServiceArea::all();
        return view('admin.categories.edit', compact('category', 'serviceAreas'));
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'category_name' => 'required|string|max:255',
            'category_slug' => 'required|unique:categories,category_slug,' . $category->id,
            'parent_category' => 'required|exists:service_areas,id',
            'category_description' => 'nullable|string',
            'category_image' => 'nullable|image|max:2048'
        ]);

        $category->fill($request->all());
        if ($request->hasFile('category_image')) {
            $category->category_image = $request->file('category_image')->store('category_images', 'public');
        }
        $category->save();

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }

    public function viewServices($id)
    {
        $category = Category::findOrFail($id);
        $services = Service::where('category_id', $category->id)->get();

        return view('admin.categories.services', compact('category', 'services'));
    }

    public function showServices($categoryId)
    {
        $category = Category::findOrFail($categoryId);
        $services = $category->services; // Assuming the relationship is defined on the Category model

        return response()->json([
            'services' => $services
        ]);
    }

}
