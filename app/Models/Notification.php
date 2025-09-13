<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'order_id',
        'customer_id', 
        'type',
        'status',
        'total_cents',
        'channel',
        'data',
        'sent_at',
        'error_message'
    ];

    protected $casts = [
        'data' => 'array',
        'sent_at' => 'datetime'
    ];

    
    const TYPE_ORDER_SUCCESS = 'order_success';
    const TYPE_ORDER_FAILURE = 'order_failure';
    const TYPE_PAYMENT_SUCCESS = 'payment_success';
    const TYPE_PAYMENT_FAILURE = 'payment_failure';

    
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';

    
    const CHANNEL_EMAIL = 'email';
    const CHANNEL_LOG = 'log';
    const CHANNEL_SMS = 'sms';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now()
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage
        ]);
    }
}
