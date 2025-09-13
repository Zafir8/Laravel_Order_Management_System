<?php

namespace App\Repositories\Impl;

use App\Models\Order;
use App\Repositories\OrderRepository;

class OrderRepositoryImpl implements OrderRepository
{
    /**
     * Find an order by its ID.
     */
    public function findById(int $id): ?Order
    {
        return Order::find($id);
    }

    /**
     * Find an order by external reference.
     */
    public function findByExternalRef(string $externalRef): ?Order
    {
        return Order::where('external_ref', $externalRef)->first();
    }

    /**
     * Create a new order.
     */
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    /**
     * Update an existing order.
     */
    public function update(Order $order, array $data): Order
    {
        $order->fill($data);
        $order->save();
        return $order;
    }

    /**
     * Delete an order.
     */
    public function delete(Order $order): bool
    {
        return $order->delete();
    }
}
