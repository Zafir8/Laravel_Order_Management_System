<?php

namespace App\Services\Impl;

use App\Models\Order;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;

/**
 * Implementation of InventoryService.
 * Handles reserving, committing, and releasing product stock.
 */
class InventoryServiceImpl implements InventoryService
{
    /**
     * Ensure all items in the order have enough available stock.
     *
     * @param Order $order
     * @throws \RuntimeException
     */
    public function ensureReservable(Order $order): void
    {
        foreach ($order->items as $item) {
            $product = $item->product()->lockForUpdate()->first();
            if (!$product) {
                throw new \RuntimeException("Product {$item->product_id} not found.");
            }

            $available = $product->stock - $product->reserved;
            if ($available < $item->qty) {
                throw new \RuntimeException("Insufficient stock for SKU {$product->sku}. Need {$item->qty}, available {$available}.");
            }
        }
    }

    /**
     * Reserve stock for order items.
     *
     * @param Order $order
     */
    public function reserveFor(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $product = $item->product()->lockForUpdate()->first();
                $available = $product->stock - $product->reserved;

                if ($available < $item->qty) {
                    throw new \RuntimeException("Insufficient stock for SKU {$product->sku}.");
                }

                $product->reserved += $item->qty;
                $product->save();
            }

            $order->status = Order::S_RESERVED;
            $order->save();
        }, 3);
    }

    /**
     * Commit stock (finalize order).
     *
     * @param Order $order
     */
    public function commitFor(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $product = $item->product()->lockForUpdate()->first();

                $product->reserved -= $item->qty;
                if ($product->reserved < 0) {
                    $product->reserved = 0;
                }

                $product->stock -= $item->qty;
                if ($product->stock < 0) {
                    $product->stock = 0;
                }

                $product->save();
            }
        }, 3);
    }

    /**
     * Release stock (rollback order).
     *
     * @param Order $order
     */
    public function releaseFor(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $product = $item->product()->lockForUpdate()->first();

                $product->reserved -= $item->qty;
                if ($product->reserved < 0) {
                    $product->reserved = 0;
                }

                $product->save();
            }
        }, 3);
    }
}
