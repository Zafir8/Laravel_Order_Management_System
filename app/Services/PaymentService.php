<?php

namespace App\Services;

use App\Models\Order;

/**
 * Service for simulating payments.
 * In a real system, this would integrate with a payment gateway.
 */
interface PaymentService
{
    /**
     * Simulate payment initiation.
     * Should generate a payment reference and store mapping to order.
     *
     * @param Order $order
     * @return string  The payment reference
     */
    public function simulateInitiate(Order $order): string;

    /**
     * Resolve an order ID from a given payment reference.
     *
     * @param string $paymentRef
     * @return int|null  Order ID if found, null otherwise
     */
    public function resolveOrderIdFromPaymentRef(string $paymentRef): ?int;
}
