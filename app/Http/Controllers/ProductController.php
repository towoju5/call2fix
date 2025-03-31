<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        $category = $request->input('category', null);
        $products = $this->productService->getProducts($category);
        // $products = \DB::table('products')->where('seller_id', '!=', auth()->id())->paginate(10);
        return get_success_response($products);
    }

    public function show($id)
    {
        $product = $this->productService->getProduct($id);
        return get_success_response($product);
    }

    public function myProducts(Request $request)
    {
        try {
            $userId = Auth::id();
            $category = $request->input('category', null);
            $products = $this->productService->getUserProducts($category, $userId);
            return get_success_response($products);
        } catch (\Exception $e) {
            return get_error_response($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $request->merge(["sku" => generate_uuid()]);
            
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required_if:rentable_price,null|numeric|min:0',
                'rentable_price' => 'required_if:price,null|array', // Changed to array for validation
                'rentable_price.days' => 'nullable|numeric|min:0',
                'rentable_price.weekly' => 'nullable|numeric|min:0',
                'rentable_price.months' => 'nullable|numeric|min:0',
                'category_id' => 'required|exists:categories,id',
                'stock' => 'required|integer|min:0',
                'sku' => 'required|string|unique:products,sku',
                'weight' => 'nullable|numeric|min:0',
                'dimensions' => 'nullable|string',
                'is_active' => 'boolean',
                'is_leasable' => 'boolean',
                'product_currency' => 'required|string|max:3',
                'product_location' => 'required|string',
                'product_longitude' => 'required|string',
                'product_latitude' => 'required|string',
                'product_image' => 'required|array',
                'product_image.*' => 'required|url', // Validate each item in the array as a valid URL
            ]);
    
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
    
            // Create the product using validated data
            $product = $this->productService->createProduct($validator->validated(), Auth::id());
    
            return get_success_response($product, "New Product added successfully", 201);
        } catch (\Exception $e) {
            return get_error_response($e->getMessage());
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            
            $request->merge(["sku" => generate_uuid()]);
            
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required_if:rentable_price,null|numeric|min:0',
                'rentable_price' => 'required_if:price,null|array', // Changed to array for validation
                'rentable_price.days' => 'nullable|numeric|min:0',
                'rentable_price.weekly' => 'nullable|numeric|min:0',
                'rentable_price.months' => 'nullable|numeric|min:0',
                'category_id' => 'required|exists:categories,id',
                'stock' => 'required|integer|min:0',
                'sku' => 'required|string|unique:products,sku',
                'weight' => 'nullable|numeric|min:0',
                'dimensions' => 'nullable|string',
                'is_active' => 'boolean',
                'is_leasable' => 'boolean',
                'product_currency' => 'required|string|max:3',
                'product_location' => 'required|string',
                'product_longitude' => 'required|string',
                'product_latitude' => 'required|string',
                'product_image' => 'required|array',
                'product_image.*' => 'required|url', // Validate each item in the array as a valid URL
            ]);
    
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $updatedProduct = $this->productService->updateProduct($product, $validator->validated());
            return get_success_response($updatedProduct, 'Product updated successfully');
        } catch (\Exception $e) {
            return get_error_response($e->getMessage(), []);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);

            $this->productService->deleteProduct($product);
            return get_success_response(null, "Product trashed successfully", 204);
        } catch (\Exception $e) {
            return get_error_response($e->getMessage());
        }
    }

    public function topProducts()
    {
        try {
            $products = $this->productService->getTopProducts();
            return get_success_response($products);
        } catch (\Exception $e) {
            return get_error_response($e->getMessage());
        }
    }
}
