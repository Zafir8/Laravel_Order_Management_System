<?php

namespace App\Services\Impl;

use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Fake payment gateway implementation.
 * Uses cache to simulate payment reference <-> order mapping.
 */
class PaymentServiceImpl implements PaymentService
{
    /** @var int TTL for payment reference mapping (seconds) */
    private int $ttl = 3600; // 1 hour

    /**
     * Simulate payment initiation and return a payment reference.
     *
     * @param Order $order
     * @return string
     */
    public function simulateInitiate(Order $order): string
    {
        $paymentRef = 'pay_' . Str::random(16);

        Cache::put(
            $this->cacheKey($paymentRef),
            $order->id,
            $this->ttl
        );

        return $paymentRef;
    }

    /**
     * Resolve a payment reference back to its order ID.
     *
     * @param string $paymentRef
     * @return int|null
     */
    public function resolveOrderIdFromPaymentRef(string $paymentRef): ?int
    {
        return Cache::get($this->cacheKey($paymentRef));
    }

    /**
     * Internal helper to generate cache key.
     */
    private function cacheKey(string $paymentRef): string
    {
        return "payment:ref:{$paymentRef}";
    }
}
