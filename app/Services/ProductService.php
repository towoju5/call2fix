<?php

namespace App\Services;

use App\Models\Product;

class ProductService
{
    public function getProducts($category = null)
    {
        $query = Product::query();
        if ($category) {
            $query->whenHas('category_id', function ($q) use ($category) {
                $q->where('name', $category);
            });
        }
        // $query = $query->whereSellerId(auth()->id())->where('is_active', true);

        // Add sorting options
        if (request('sort') === 'latest') {
            $query->latest();
        } elseif (request('sort') === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif (request('sort') === 'price_desc') {
            $query->orderBy('price', 'desc');
        } elseif (request('sort') === 'top_purchased') {
            $query->withCount(['orders' => function ($query) {
                $query->select('product_id')->groupBy('product_id');
            }])->orderBy('orders_count', 'desc');
        }

        return $query->get();
    }

    public function getUserProducts($category = null)
    {
        $query = Product::query();
        if ($category) {
            $query->whenHas('category_id', function ($q) use ($category) {
                $q->where('name', $category);
            });
        }
        $query = $query->whereSellerId(auth()->id())->where('is_active', true);

        // Add sorting options
        if (request('sort') === 'latest') {
            $query->latest();
        } elseif (request('sort') === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif (request('sort') === 'price_desc') {
            $query->orderBy('price', 'desc');
        } elseif (request('sort') === 'top_purchased') {
            $query->withCount(['orders' => function ($query) {
                $query->select('product_id')->groupBy('product_id');
            }])->orderBy('orders_count', 'desc');
        }

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
