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
        $category = $request->category;
        $products = $this->productService->getProducts($category);
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
            $products = $this->productService->getUserProducts($userId);
            return get_success_response($products);
        } catch (\Exception $e) {
            return get_error_response($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $product = Product::count();
            $request->merge(["sku" => generate_uuid()]);
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required_if:rentable_price,null|numeric|min:0',
                'rentable_price' => 'required_if:price,null|numeric|min:0',
                'rentable_price.*' => 'sometimes|required_with:rentable_price.days,rentable_price.weekly,rentable_price.months|array', 
                'category_id' => 'required|exists:categories,id',
                'stock' => 'required|integer|min:0',
                'sku' => 'required|string|unique:products,sku',
                'weight' => 'nullable|numeric|min:0',
                'dimensions' => 'nullable|string',
                'is_active' => 'boolean',
                'is_leasable' => 'boolean',
                'product_currency' => 'required|string|max:3',
                'product_location' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            if ($validator->fails()) {
                return get_error_response("Validation error", $validator->errors(), 422);
            }

            $product = $this->productService->createProduct($validator->validated(), Auth::id());
            return get_success_response($product, "New Product added successfuly", 201);
        } catch (\Exception $e) {
            return get_error_response($e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            // $request->user()->can('update products');

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required_if:rentable_price,null|numeric|min:0',
                'rentable_price' => 'required_if:price,null|numeric|min:0',
                'rentable_price.*' => 'sometimes|required_with:rentable_price.days,rentable_price.weekly,rentable_price.months|array', 
                'category_id' => 'required|exists:categories,id',
                'stock' => 'required|integer|min:0',
                'sku' => 'required|string|unique:products,sku',
                'weight' => 'nullable|numeric|min:0',
                'dimensions' => 'nullable|string',
                'is_active' => 'boolean',
                'is_leasable' => 'boolean',
                'product_currency' => 'required|string|max:3',
                'product_location' => 'required|string',
            ]);

            if ($validator->fails()) {
                return get_error_response("Validation error", $validator->errors(), 422);
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
}