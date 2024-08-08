<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->can('view categories')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $categories = Category::all();
            return view('admin.categories.index', compact('categories'));
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while fetching categories.');
        }
    }

    public function create(Request $request)
    {
        if (!$request->user()->can('create categories')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $parentCategories = Category::whereNull('parent_category')->pluck('category_name', 'id');
            return view('admin.categories.create', compact('parentCategories'));
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while loading the create form.');
        }
    }

    public function store(Request $request)
    {
        if (!$request->user()->can('create categories')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $validatedData = $request->validate([
                'parent_category' => 'nullable|exists:categories,id',
                'category_name' => 'required|string|max:255',
                'category_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'category_description' => 'nullable|string',
            ]);

            $validatedData['category_slug'] = Str::slug($validatedData['category_name']);

            if ($request->hasFile('category_image')) {
                $imagePath = $request->file('category_image')->store('category_images', 'public');
                $validatedData['category_image'] = $imagePath;
            }

            Category::create($validatedData);

            return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while creating the category.');
        }
    }

    public function show(Request $request, Category $category)
    {
        if (!$request->user()->can('view categories')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            return view('admin.categories.show', compact('category'));
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while showing the category.');
        }
    }

    public function edit(Request $request, Category $category)
    {
        if (!$request->user()->can('edit categories')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $parentCategories = Category::whereNull('parent_category')->where('id', '!=', $category->id)->pluck('category_name', 'id');
            return view('admin.categories.edit', compact('category', 'parentCategories'));
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while loading the edit form.');
        }
    }

    public function update(Request $request, Category $category)
    {
        if (!$request->user()->can('edit categories')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $validatedData = $request->validate([
                'parent_category' => 'nullable|exists:categories,id',
                'category_name' => 'required|string|max:255',
                'category_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'category_description' => 'nullable|string',
            ]);

            $validatedData['category_slug'] = Str::slug($validatedData['category_name']);

            if ($request->hasFile('category_image')) {
                $imagePath = $request->file('category_image')->store('category_images', 'public');
                $validatedData['category_image'] = $imagePath;
            }

            $category->update($validatedData);

            return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while updating the category.');
        }
    }

    public function destroy(Request $request, Category $category)
    {
        if (!$request->user()->can('delete categories')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $category->delete();
            return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while deleting the category.');
        }
    }
}
