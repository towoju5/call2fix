<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    public function create(User $user)
    {
        return $user->hasPermissionTo('create products');
    }

    public function update(User $user, Product $product)
    {
        return $user->id === $product->seller_id || $user->hasPermissionTo('edit products');
    }

    public function delete(User $user, Product $product)
    {
        return $user->id === $product->seller_id || $user->hasPermissionTo('delete products');
    }

}
