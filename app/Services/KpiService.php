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
     * Retrieve KPIs for a given day.
     *
     * @param CarbonInterface|null $day  Defaults to today
     * @return array{
     *   revenue_cents: int,
     *   order_count: int,
     *   average_order_value_cents: int
     * }
     */
    public function getDailyKpis(?CarbonInterface $day = null): array;
}
