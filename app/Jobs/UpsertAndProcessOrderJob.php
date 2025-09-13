<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\OrderWorkflowService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpsertAndProcessOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $rowData;
    protected string $batchId;

    /**
     * Create a new job instance.
     *
     * @param array $rowData  One row parsed from CSV
     * @param string $batchId Batch identifier
     */
    public function __construct(array $rowData, string $batchId)
    {
        $this->rowData = $rowData;
        $this->batchId = $batchId;
    }

    /**
     * Execute the job.
     */
    public function handle(OrderWorkflowService $workflowService): void
    {
        try {
            // Build payload from transformed CSV data
            $orderPayload = [
                'external_ref' => $this->rowData['external_ref'],
                'customer_id'  => $this->rowData['customer_id'],
                'total_cents'  => (int)$this->rowData['total_cents'],
                'items'        => $this->normalizeItems($this->rowData['items']),
            ];

            // 1. Upsert order + items
            $order = $workflowService->upsertOrder($orderPayload);

            // 2. Reserve stock
            $workflowService->reserveStock($order);

            // 3. Simulate payment initiation (real system would be async callback)
            $paymentRef = $workflowService->initiatePayment($order);

            // 4. Fake immediate payment success callback (can be queued separately)
            ProcessPaymentCallbackJob::dispatch($paymentRef, true);

        } catch (\Throwable $e) {
            Log::error("Failed processing order in batch {$this->batchId}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Normalize items data - handle both array and string formats
     */
    private function normalizeItems($items): array
    {
        if (is_array($items)) {
            // New format: already an array of items
            return array_map(function($item) {
                return [
                    'product_id' => (int)$item['product_id'],
                    'quantity' => (int)$item['quantity'],
                    'price_cents' => (int)($item['unit_price_cents'] ?? $item['price_cents']),
                ];
            }, $items);
        }
        
        // Legacy format: string that needs parsing
        return $this->parseItems($items);
    }

    /**
     * Parse order items from CSV row.
     *
     * Expected format for items column:
     *   product_id:qty:price|product_id:qty:price
     *
     * @param string $itemsStr
     * @return array<int, array{product_id:int, quantity:int, price_cents:int}>
     */
    private function parseItems(string $itemsStr): array
    {
        $items = [];
        $parts = explode('|', $itemsStr);

        foreach ($parts as $part) {
            [$productId, $qty, $price] = explode(':', $part);
            $items[] = [
                'product_id'  => (int)$productId,
                'quantity'    => (int)$qty,
                'price_cents' => (int)$price,
            ];
        }

        return $items;
    }
}
