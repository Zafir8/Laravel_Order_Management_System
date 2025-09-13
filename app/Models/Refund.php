<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    protected $fillable = [
        'order_id',
        'refund_reference',
        'amount_cents',
        'type',
        'status',
        'reason',
        'metadata',
        'processed_at',
        'failure_reason'
    ];

    protected $casts = [
        'metadata' => 'array',
        'processed_at' => 'datetime'
    ];

    // Refund types
    const TYPE_FULL = 'full';
    const TYPE_PARTIAL = 'partial';

    // Refund status
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSED = 'processed';
    const STATUS_FAILED = 'failed';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Check if this is a full refund
     */
    public function isFullRefund(): bool
    {
        return $this->type === self::TYPE_FULL;
    }

    /**
     * Check if this is a partial refund
     */
    public function isPartialRefund(): bool
    {
        return $this->type === self::TYPE_PARTIAL;
    }

    /**
     * Mark refund as processed
     */
    public function markAsProcessed(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSED,
            'processed_at' => now()
        ]);
    }

    /**
     * Mark refund as failed
     */
    public function markAsFailed(string $reason): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'failure_reason' => $reason
        ]);
    }

    /**
     * Generate a unique refund reference for idempotency
     */
    public static function generateRefundReference(int $orderId, int $amountCents): string
    {
        return 'refund_' . $orderId . '_' . $amountCents . '_' . now()->timestamp;
    }

    /**
     * Find existing refund by reference (for idempotency)
     */
    public static function findByReference(string $refundReference): ?self
    {
        return static::where('refund_reference', $refundReference)->first();
    }

    /**
     * Get total refunded amount for an order
     */
    public static function getTotalRefundedForOrder(int $orderId): int
    {
        return static::where('order_id', $orderId)
            ->where('status', self::STATUS_PROCESSED)
            ->sum('amount_cents');
    }

    /**
     * Check if order can be refunded for the given amount
     */
    public static function canRefundOrder(Order $order, int $amountCents): bool
    {
        $totalRefunded = static::getTotalRefundedForOrder($order->id);
        $remainingRefundable = $order->total_cents - $totalRefunded;
        
        return $amountCents <= $remainingRefundable;
    }
}
