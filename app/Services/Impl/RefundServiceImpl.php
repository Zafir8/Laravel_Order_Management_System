<?php

namespace App\Services\Impl;

use App\Jobs\ProcessRefundJob;
use App\Models\Order;
use App\Models\Refund;
use App\Services\RefundService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Implementation of RefundService.
 * Handles refund logic with idempotency and async processing.
 */
class RefundServiceImpl implements RefundService
{
    /**
     * Process a full refund for an order.
     */
    public function processFullRefund(Order $order, string $refundReference, ?string $reason = null): Refund
    {
        return $this->processRefund($order, $order->total_cents, $refundReference, $reason);
    }

    /**
     * Process a partial refund for an order.
     */
    public function processPartialRefund(Order $order, int $amountCents, string $refundReference, ?string $reason = null): Refund
    {
        if ($amountCents <= 0) {
            throw new Exception('Refund amount must be greater than zero');
        }

        if ($amountCents > $order->total_cents) {
            throw new Exception('Refund amount cannot exceed order total');
        }

        return $this->processRefund($order, $amountCents, $refundReference, $reason);
    }

    /**
     * Get total amount refunded for an order.
     */
    public function getTotalRefunded(Order $order): int
    {
        return Refund::getTotalRefundedForOrder($order->id);
    }

    /**
     * Check if an order can be refunded.
     */
    public function canRefund(Order $order, ?int $requestedAmountCents = null): bool
    {
        // If no specific amount requested, check if any amount can be refunded
        if ($requestedAmountCents === null) {
            $totalRefunded = Refund::getTotalRefundedForOrder($order->id);
            return $totalRefunded < $order->total_cents;
        }
        
        return Refund::canRefundOrder($order, $requestedAmountCents);
    }

    /**
     * Internal method to handle refund processing with idempotency.
     */
    private function processRefund(Order $order, int $amountCents, string $refundReference, ?string $reason): Refund
    {
        try {
            return DB::transaction(function () use ($order, $amountCents, $refundReference, $reason) {
                // Check for existing refund with same reference (idempotency)
                $existingRefund = Refund::where('refund_reference', $refundReference)->first();
                if ($existingRefund) {
                    Log::info('Refund already exists for reference', [
                        'refund_reference' => $refundReference,
                        'refund_id' => $existingRefund->id
                    ]);
                    return $existingRefund;
                }

                // Validate refund can be processed
                if (!$this->canRefund($order, $amountCents)) {
                    throw new Exception('Refund amount exceeds remaining refundable amount for order');
                }

                // Determine refund type
                $type = ($amountCents >= $order->total_cents) ? 'full' : 'partial';

                // Create refund record
                $refund = Refund::create([
                    'order_id' => $order->id,
                    'refund_reference' => $refundReference,
                    'amount_cents' => $amountCents,
                    'type' => $type,
                    'reason' => $reason,
                    'status' => 'pending',
                ]);

                // Dispatch async job for processing
                ProcessRefundJob::dispatch(
                    $refund->refund_reference,
                    $order->id,
                    $refund->amount_cents,
                    $refund->reason ?? 'Customer refund request'
                );

                Log::info('Refund created and queued for processing', [
                    'refund_id' => $refund->id,
                    'order_id' => $order->id,
                    'amount_cents' => $amountCents,
                    'type' => $type
                ]);

                return $refund;
            });
        } catch (Exception $e) {
            Log::error('Failed to process refund', [
                'order_id' => $order->id,
                'refund_reference' => $refundReference,
                'amount_cents' => $amountCents,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}