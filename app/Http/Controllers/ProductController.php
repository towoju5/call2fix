<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function store(Request $request)
    {
        try {
            $this->authorize('create products');
            $product = Product::count();
            $request->sku = "SKU-" . ((int) $product + 1230);

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'category_id' => 'required|exists:categories,id',
                'stock' => 'required|integer|min:0',
                'sku' => 'required|string|unique:products,sku',
                'weight' => 'nullable|numeric|min:0',
                'dimensions' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $product = $this->productService->createProduct($validatedData, Auth::id());
            return get_success_response($product, "New Product added successfuly", 201);
        } catch (\Exception $e) {
            return get_error_response($e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            $this->authorize('update products');

            $validatedData = $request->validate([
                'name' => 'string|max:255',
                'description' => 'string',
                'price' => 'numeric|min:0',
                'category_id' => 'exists:categories,id',
                'stock' => 'integer|min:0',
                'weight' => 'nullable|numeric|min:0',
                'dimensions' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $updatedProduct = $this->productService->updateProduct($product, $validatedData);
            return get_success_response($updatedProduct);
        } catch (\Exception $e) {
            return get_error_response($e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $this->authorize('delete products');

            $this->productService->deleteProduct($product);
            return get_success_response(null, "Product trashed successfully", 204);
        } catch (\Exception $e) {
            return get_error_response($e->getMessage());
        }
    }
}