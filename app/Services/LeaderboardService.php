<?php

namespace App\Services;

use App\Models\Order;

/**
 * Service for maintaining a leaderboard of top customers by revenue.
 * Uses Redis sorted sets for efficient ranking.
 */
interface LeaderboardService
{
    /**
     * Increment the customer's leaderboard score
     * when an order is finalized.
     *
     * @param Order $order
     */
    public function bumpCustomerScore(Order $order): void;

    /**
     * Fetch the top N customers ranked by revenue.
     *
     * @param int $limit
     * @return array<int, array{customer_id:int, score:int}>
     */
    public function topCustomers(int $limit = 10): array;
}
