<?php

namespace App\Services;

use App\Models\Order;
use Carbon\CarbonInterface;

/**
 * Service for tracking and retrieving daily KPIs.
 * Uses Redis for fast storage and aggregation.
 */
interface KpiService
{
    /**
     * Track metrics when an order is finalized.
     *
     * @param Order $order
     * @param CarbonInterface|null $when  Date/time for attribution (defaults to now)
     */
    public function trackFinalized(Order $order, ?CarbonInterface $when = null): void;

    /**
     * Track refund metrics - subtract from revenue and track refund data.
     *
     * @param string $day Date in Y-m-d format
     * @param int $refundAmountCents Amount refunded in cents
     */
    public function trackRefund(string $day, int $refundAmountCents): void;

    /**
     * Retrieve KPIs for a given day.
     *
     * @param CarbonInterface|null $day  Defaults to today
     * @return array{
     *   revenue_cents: int,
     *   order_count: int,
     *   refund_count: int,
     *   refund_amount_cents: int,
     *   gross_revenue_cents: int,
     *   average_order_value_cents: int
     * }
     */
    public function getDailyKpis(?CarbonInterface $day = null): array;
}
