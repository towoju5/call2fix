<?php

namespace App\Services;

use App\Models\Product;
use DB;

class ProductService
{
    public function getProducts($category = null)
    {
        $query = Product::query();

        if ($category) {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('name', $category);
            });
        }
        
        // Exclude products from the authenticated seller
        $query->where('seller_id', '!=', auth()->id());
        
        // Add sorting options
        if (request('sort') === 'latest') {
            $query->latest();
        } elseif (request('sort') === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif (request('sort') === 'price_desc') {
            $query->orderBy('price', 'desc');
        } elseif (request('sort') === 'top_purchased') {
            $query->withCount(['orders'])->orderBy('orders_count', 'desc');
        }
        
        // Fetch only active products
        return $query->where('is_active', true)->get();

    }

    public function getUserProducts($category = null, $sellerId = null)
    {
        $query = Product::query();

        if ($category) {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('name', $category);
            });
        }
        
        // Exclude products from the authenticated seller
        $query->where('seller_id', $sellerId);
        
        // Add sorting options
        if (request('sort') === 'latest') {
            $query->latest();
        } elseif (request('sort') === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif (request('sort') === 'price_desc') {
            $query->orderBy('price', 'desc');
        } elseif (request('sort') === 'top_purchased') {
            $query->withCount(['orders'])->orderBy('orders_count', 'desc');
        }
        
        // Fetch only active products
        return $query->get();
    }

    public function getProduct($id)
    {
        return Product::findOrFail($id);
    }

    public function createProduct($data, $sellerId)
    {
        $data['seller_id'] = $sellerId;
        return Product::create($data);
    }

    public function updateProduct(Product $product, $data)
    {
        $product->update($data);
        return $product;
    }

    public function getTopProducts()
    {
        $products = Product::withCount('orders')->orderBy('orders_count', 'desc')->take(20)->get();
        return $products;
    }

    public function deleteProduct(Product $product)
    {
        return $product->delete();
    }
}
