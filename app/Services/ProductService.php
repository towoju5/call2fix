<?php

namespace App\Services;

use App\Models\Product;

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
        return $query->where('is_active', true)->get();
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

    public function deleteProduct(Product $product)
    {
        return $product->delete();
    }
}
