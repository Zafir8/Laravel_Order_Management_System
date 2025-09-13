<?php

namespace App\Repositories;

use App\Models\OrderItem;

interface OrderItemRepository
{
    /**
     * Find an order item by ID.
     *
     * @param int $id
     * @return OrderItem|null
     */
    public function findById(int $id): ?OrderItem;

    /**
     * Create a new order item.
     *
     * @param array $data
     * @return OrderItem
     */
    public function create(array $data): OrderItem;

    /**
     * Update an existing order item.
     *
     * @param OrderItem $orderItem
     * @param array $data
     * @return OrderItem
     */
    public function update(OrderItem $orderItem, array $data): OrderItem;

    /**
     * Delete an order item.
     *
     * @param OrderItem $orderItem
     * @return bool
     */
    public function delete(OrderItem $orderItem): bool;
}
