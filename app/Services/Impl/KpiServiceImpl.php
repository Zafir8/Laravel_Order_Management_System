<?php

namespace App\Services\Impl;

use App\Models\Order;
use App\Services\KpiService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Redis;

/**
 * KPI Service implementation.
 * Tracks revenue, order count, and average order value in Redis.
 */
class KpiServiceImpl implements KpiService
{
    /**
     * Increment KPIs when an order is finalized.
     */
    public function trackFinalized(Order $order, ?CarbonInterface $when = null): void
    {
        $day = ($when ?? Carbon::now())->format('Y-m-d');
        $key = $this->kpiKey($day);

        // increment counters
        Redis::hincrby($key, 'revenue_cents', $order->total_cents);
        Redis::hincrby($key, 'order_count', 1);

        // set TTL so data eventually expires (optional, e.g., 90 days)
        Redis::expire($key, 60 * 60 * 24 * 90);
    }

    /**
     * Retrieve KPIs for a given day.
     */
    public function getDailyKpis(?CarbonInterface $day = null): array
    {
        $day = ($day ?? Carbon::now())->format('Y-m-d');
        $key = $this->kpiKey($day);

        $data = Redis::hgetall($key);

        $revenue = (int)($data['revenue_cents'] ?? 0);
        $count   = (int)($data['order_count'] ?? 0);
        $aov     = $count > 0 ? intdiv($revenue, $count) : 0;

        return [
            'revenue_cents'             => $revenue,
            'order_count'               => $count,
            'average_order_value_cents' => $aov,
        ];
    }

    /**
     * Build the Redis key for a given day.
     */
    private function kpiKey(string $day): string
    {
        return "kpi:daily:{$day}";
    }
}
