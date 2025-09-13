<?php

namespace App\Jobs;

use App\Jobs\UpsertAndProcessOrderJob;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImportCsvStreamJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $csvPath;
    protected string $batchId;

    /**
     * Create a new job instance.
     *
     * @param string $csvPath  Full path to the CSV file
     * @param string $batchId  Unique identifier for the import batch
     */
    public function __construct(string $csvPath, string $batchId)
    {
        $this->csvPath = $csvPath;
        $this->batchId = $batchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!file_exists($this->csvPath)) {
            throw new \RuntimeException("CSV file not found at {$this->csvPath}");
        }

        $handle = fopen($this->csvPath, 'r');
        if (!$handle) {
            throw new \RuntimeException("Unable to open CSV file: {$this->csvPath}");
        }

        $header = null;
        $lineNo = 0;
        $orderGroups = [];

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $lineNo++;

            // first row = header
            if ($lineNo === 1) {
                $header = $row;
                continue;
            }

            if (!$header) {
                continue;
            }

            $data = array_combine($header, $row);
            if (!$data) {
                continue;
            }

            try {
                // Transform raw CSV data into processed order data
                $transformedData = $this->transformCsvRow($data);
                
                // Group by customer and order date to combine items into single orders
                $orderKey = $transformedData['customer_id'] . '_' . $transformedData['order_date'];
                
                if (!isset($orderGroups[$orderKey])) {
                    $orderGroups[$orderKey] = [
                        'external_ref' => $transformedData['external_ref'],
                        'customer_id' => $transformedData['customer_id'],
                        'total_cents' => 0,
                        'items' => [],
                        'order_date' => $transformedData['order_date']
                    ];
                }
                
                // Add item to order
                $orderGroups[$orderKey]['items'][] = $transformedData['item'];
                $orderGroups[$orderKey]['total_cents'] += $transformedData['item']['total_price_cents'];
                
            } catch (\Exception $e) {
                Log::error("Failed processing CSV row in batch {$this->batchId}: " . $e->getMessage(), [
                    'line' => $lineNo,
                    'data' => $data
                ]);
            }
        }

        fclose($handle);

        // Dispatch jobs for each unique order
        foreach ($orderGroups as $orderData) {
            UpsertAndProcessOrderJob::dispatch($orderData, $this->batchId);
        }
    }

    /**
     * Transform raw CSV row into processed order data
     */
    private function transformCsvRow(array $csvRow): array
    {
        // Get or create customer
        $customer = Customer::firstOrCreate(
            ['email' => $csvRow['customer_email']],
            ['name' => $csvRow['customer_name']]
        );

        // Get or create product
        $priceCents = (int)(floatval($csvRow['product_price']) * 100);
        $product = Product::firstOrCreate(
            ['name' => $csvRow['product_name']],
            ['price_cents' => $priceCents]
        );

        $quantity = (int)$csvRow['quantity'];
        $totalPriceCents = $priceCents * $quantity;

        return [
            'external_ref' => 'CSV_' . $this->batchId . '_' . Str::random(8),
            'customer_id' => $customer->id,
            'order_date' => $csvRow['order_date'] ?? now()->format('Y-m-d'),
            'item' => [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price_cents' => $priceCents,
                'total_price_cents' => $totalPriceCents
            ]
        ];
    }
}
