<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Refund;

/**
 * Service for handling order refunds (full or partial).
 * Coordinates async processing with analytics updates.
 */
interface RefundService
{
    /**
     * Process a full refund for an order.
     * Returns existing refund if already processed (idempotent).
     *
     * @param Order $order
     * @param string $refundReference Unique reference for idempotency
     * @param string|null $reason Optional refund reason
     * @return Refund
     * @throws \Exception if refund cannot be processed
     */
    public function processFullRefund(Order $order, string $refundReference, ?string $reason = null): Refund;

    /**
     * Process a partial refund for an order.
     * Returns existing refund if already processed (idempotent).
     *
     * @param Order $order
     * @param int $amountCents Amount to refund in cents
     * @param string $refundReference Unique reference for idempotency
     * @param string|null $reason Optional refund reason
     * @return Refund
     * @throws \Exception if refund cannot be processed
     */
    public function processPartialRefund(Order $order, int $amountCents, string $refundReference, ?string $reason = null): Refund;

    /**
     * Get total amount refunded for an order.
     *
     * @param Order $order
     * @return int Total refunded amount in cents
     */
    public function getTotalRefunded(Order $order): int;

    /**
     * Check if an order can be refunded (has remaining refundable amount).
     *
     * @param Order $order
     * @param int|null $requestedAmountCents Optional specific amount to check
     * @return bool
     */
    public function canRefund(Order $order, ?int $requestedAmountCents = null): bool;
}