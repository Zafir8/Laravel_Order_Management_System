<?php

namespace App\Services\Impl;

use App\Models\Order;
use App\Services\OrderWorkflowService;
use App\Services\InventoryService;
use App\Services\PaymentService;
use App\Services\KpiService;
use App\Services\LeaderboardService;
use Illuminate\Support\Facades\DB;

class OrderWorkflowServiceImpl implements OrderWorkflowService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly PaymentService $paymentService,
        private readonly KpiService $kpiService,
        private readonly LeaderboardService $leaderboardService
    ) {}

    /**
     * Upsert (create or update) an order with items.
     */
    public function upsertOrder(array $orderPayload): Order
    {
        return DB::transaction(function () use ($orderPayload) {
            $order = Order::updateOrCreate(
                ['external_ref' => $orderPayload['external_ref']],
                [
                    'customer_id' => $orderPayload['customer_id'],
                    'status'      => Order::S_CREATED,
                    'total_cents' => $orderPayload['total_cents'],
                ]
            );

            // Replace items
            $order->items()->delete();
            foreach ($orderPayload['items'] as $item) {
                $order->items()->create([
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'price_cents' => $item['price_cents'],
                ]);
            }

            return $order;
        });
    }

    /**
     * Reserve stock for the given order.
     */
    public function reserveStock(Order $order): void
    {
        $this->inventoryService->ensureReservable($order);
        $this->inventoryService->reserveFor($order);
    }

    /**
     * Simulate payment initiation for an order.
     */
    public function initiatePayment(Order $order): string
    {
        $paymentRef = $this->paymentService->simulateInitiate($order);

        $order->payment_ref = $paymentRef;
        $order->status = Order::S_PAID;
        $order->save();

        return $paymentRef;
    }

    /**
     * Handle payment callback (success or failure).
     */
    public function handlePaymentCallback(string $paymentRef, bool $success, ?string $reason = null): void
    {
        $orderId = $this->paymentService->resolveOrderIdFromPaymentRef($paymentRef);
        if (!$orderId) {
            throw new \RuntimeException("Invalid payment reference: {$paymentRef}");
        }

        $order = Order::findOrFail($orderId);

        if ($success) {
            $this->finalize($order);
        } else {
            $this->rollback($order, $reason ?? 'Payment failed');
        }
    }

    /**
     * Finalize an order (decrement stock, mark finalized, track KPIs).
     */
    public function finalize(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $this->inventoryService->commitFor($order);

            $order->status = Order::S_FINALIZED;
            $order->save();

            $this->kpiService->trackFinalized($order);
            $this->leaderboardService->bumpCustomerScore($order);
        });
    }

    /**
     * Rollback order (release reserved stock, mark failed).
     */
    public function rollback(Order $order, ?string $reason = null): void
    {
        DB::transaction(function () use ($order, $reason) {
            $this->inventoryService->releaseFor($order);

            $order->status = Order::S_FAILED;
            $order->save();

            // optionally log $reason somewhere
        });
    }
}
