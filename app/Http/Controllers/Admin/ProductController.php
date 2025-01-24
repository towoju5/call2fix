<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;


class ProductController extends Controller
{
    public function index()
    {
        $products = Product::paginate(15);
        $categories = Category::all();
        $sellers = User::all();
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        $sellers = User::role('suppliers')->get();
        return view('admin.products.create', compact('categories', 'sellers'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required_if:rentable_price,null|numeric|min:0',
            'rentable_price' => 'required_if:price,null|numeric|min:0',
            'rentable_price.*' => 'sometimes|required_with:rentable_price.days,rentable_price.weekly,rentable_price.months|array', 
            'category_id' => 'required|exists:categories,id',
            'seller_id' => 'required|exists:users,id',
            'stock' => 'required|integer|min:0',
            'sku' => 'required|string|unique:products',
            'product_currency' => 'required|string',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string',
            'is_active' => 'boolean',
            'is_leasable' => 'boolean',
            'product_image' => 'required|array',
            'product_image.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $product = Product::create($validator->validated());

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('product_images', 'public');
                $product->images()->create(['path' => $path]);
            }
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $sellers = User::all();
        return view('admin.products.edit', compact('product', 'categories', 'sellers'));
    }

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'seller_id' => 'required|exists:users,id',
            'stock' => 'required|integer|min:0',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'product_currency' => 'required|string',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string',
            'is_active' => 'boolean',
            'is_leasable' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $product->update($validator->validated());

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }



    public function orderIndex(Request $request)
    {
        if ($request->ajax()) {
            $query = Product::with('category');

            if ($request->has('category')) {
                $query->where('category_id', $request->category);
            }

            if ($request->has('status')) {
                $query->where('is_active', $request->status);
            }

            return Datatables::of($query)
                ->addColumn('action', function ($product) {
                    return view('admin.products.action', compact('product'))->render();
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $categories = Category::all();
        return view('admin.products.index', compact('categories'));
    }

}
