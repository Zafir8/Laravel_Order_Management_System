<?php

namespace App\Services;

use App\Models\Order;

/**
 * Handles all stock management operations
 * related to reservations, commits, and rollbacks.
 */
interface InventoryService
{
    /**
     * Ensure all products in the order can be reserved.
     *
     * @param Order $order
     * @throws \RuntimeException if stock is insufficient
     */
    public function ensureReservable(Order $order): void;

    /**
     * Reserve stock for all items in the order.
     * Increases Product.reserved and marks order as reserved.
     *
     * @param Order $order
     */
    public function reserveFor(Order $order): void;

    /**
     * Commit stock when finalizing.
     * Decreases stock and reserved quantities.
     *
     * @param Order $order
     */
    public function commitFor(Order $order): void;

    /**
     * Release stock when rolling back.
     * Decreases reserved quantities only.
     *
     * @param Order $order
     */
    public function releaseFor(Order $order): void;
}
