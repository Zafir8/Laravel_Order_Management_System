<?php

namespace App\Services;

use App\Models\Order;

/**
 * Service that manages the lifecycle of an order:
 * creation, stock reservation, payment, finalization, or rollback.
 */
interface OrderWorkflowService
{
    /**
     * Upsert (create or update) an order and its items from a parsed payload.
     *
     * @param array $orderPayload
     * @return Order
     */
    public function upsertOrder(array $orderPayload): Order;

    /**
     * Reserve stock for the given order.
     *
     * @param Order $order
     */
    public function reserveStock(Order $order): void;

    /**
     * Simulate payment initiation for an order.
     *
     * @param Order $order
     * @return string  Payment reference
     */
    public function initiatePayment(Order $order): string;

    /**
     * Handle payment callback (success or fail).
     *
     * @param string $paymentRef
     * @param bool $success
     * @param string|null $reason
     */
    public function handlePaymentCallback(string $paymentRef, bool $success, ?string $reason = null): void;

    /**
     * Finalize the order after payment succeeds.
     *
     * @param Order $order
     */
    public function finalize(Order $order): void;

    /**
     * Roll back the order (release reserved stock, mark failed).
     *
     * @param Order $order
     * @param string|null $reason
     */
    public function rollback(Order $order, ?string $reason = null): void;
}
