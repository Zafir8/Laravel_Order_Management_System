<?php

namespace App\Repositories\Impl;

use App\Models\OrderItem;
use App\Repositories\OrderItemRepository;

class OrderItemRepositoryImpl implements OrderItemRepository
{
    /**
     * Find an order item by ID.
     */
    public function findById(int $id): ?OrderItem
    {
        return OrderItem::find($id);
    }

    /**
     * Create a new order item.
     */
    public function create(array $data): OrderItem
    {
        return OrderItem::create($data);
    }

    /**
     * Update an existing order item.
     */
    public function update(OrderItem $orderItem, array $data): OrderItem
    {
        $orderItem->fill($data);
        $orderItem->save();
        return $orderItem;
    }

    /**
     * Delete an order item.
     */
    public function delete(OrderItem $orderItem): bool
    {
        return $orderItem->delete();
    }
}
