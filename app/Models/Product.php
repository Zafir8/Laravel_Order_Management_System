<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Product extends Model
{
    protected $fillable = [
        'sku',
        'name',
        'stock',
        'reserved',
        'price_cents'
    ];

    public function orderItems(): HasMany {
        return $this->hasMany(OrderItem::class);
    }
}
