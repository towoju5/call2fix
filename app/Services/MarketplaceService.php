<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Order;
use App\Models\ItemRequest;

class MarketplaceService
{
    public function getItems($category = null)
    {
        $query = Item::query();
        if ($category) {
            $query->where('category', $category);
        }
        return $query->get();
    }

    public function purchaseItem($itemId, $userId)
    {
        $item = Item::findOrFail($itemId);
        $order = Order::create([
            'user_id' => $userId,
            'item_id' => $itemId,
            'status' => 'pending'
        ]);
        return $order;
    }

    public function requestItem($itemDetails, $userId)
    {
        return ItemRequest::create([
            'user_id' => $userId,
            'details' => $itemDetails,
            'status' => 'open'
        ]);
    }

    public function listItemForSale($itemDetails, $sellerId)
    {
        return Item::create(array_merge($itemDetails, ['seller_id' => $sellerId]));
    }
}
