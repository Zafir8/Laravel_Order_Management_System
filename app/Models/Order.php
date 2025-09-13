<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const S_CREATED   = 'created';
    public const S_RESERVED  = 'reserved';
    public const S_PAID      = 'paid';
    public const S_FINALIZED = 'finalized';
    public const S_FAILED    = 'failed';

    protected $fillable = [
        'customer_id',
        'external_ref',
        'status',
        'total_cents',
        'payment_ref'
    ];

    public function customer(): BelongsTo {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany {
        return $this->hasMany(OrderItem::class);
    }

    public function notifications(): HasMany {
        return $this->hasMany(Notification::class);
    }

    public function isTerminal(): bool {
        return in_array($this->status, [self::S_FINALIZED, self::S_FAILED], true);
    }
}
