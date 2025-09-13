<?php

namespace App\Services\Impl;

use App\Models\Order;
use App\Models\Refund;
use App\Services\LeaderboardService;
use Illuminate\Support\Facades\Redis;

/**
 * Leaderboard Service implementation.
 * Uses Redis sorted sets to rank customers by total revenue.
 */
class LeaderboardServiceImpl implements LeaderboardService
{
    private string $key = 'leaderboard:customers';

    /**
     * Add revenue to a customer's score when an order is finalized.
     *
     * @param Order $order
     */
    public function bumpCustomerScore(Order $order): void
    {
        if (!$order->customer_id) {
            return;
        }

        Redis::zincrby($this->key, $order->total_cents, (string) $order->customer_id);
    }

        /**
     * Adjust the customer's leaderboard score downward
     * when a refund is processed.
     */
    public function adjustCustomerScoreForRefund(int $customerId, int $refundAmountCents): void
    {
        Redis::zincrby($this->key, -$refundAmountCents, (string) $customerId);
    }

    /**
     * Get top N customers ranked by revenue.
     *
     * @param int $limit
     * @return array<int, array{customer_id:int, score:int}>
     */
    public function topCustomers(int $limit = 10): array
    {
        // ZREVRANGE withscores => highest first
        $raw = Redis::zrevrange($this->key, 0, $limit - 1, 'WITHSCORES');

        $results = [];
        foreach ($raw as $customerId => $score) {
            $results[] = [
                'customer_id' => (int) $customerId,
                'score'       => (int) $score,
            ];
        }

        return $results;
    }
}
