<?php

namespace App\Repositories;

use App\Models\Order;

interface OrderRepository
{
    /**
     * Find an order by its ID.
     *
     * @param int $id
     * @return Order|null
     */
    public function findById(int $id): ?Order;

    /**
     * Find an order by external reference.
     *
     * @param string $externalRef
     * @return Order|null
     */
    public function findByExternalRef(string $externalRef): ?Order;

    /**
     * Create a new order with given data.
     *
     * @param array $data
     * @return Order
     */
    public function create(array $data): Order;

    /**
     * Update an existing order.
     *
     * @param Order $order
     * @param array $data
     * @return Order
     */
    public function update(Order $order, array $data): Order;

    /**
     * Delete an order.
     *
     * @param Order $order
     * @return bool
     */
    public function delete(Order $order): bool;
}
