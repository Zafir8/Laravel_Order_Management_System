<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Refund;
use App\Services\KpiService;
use App\Services\LeaderboardService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessRefundJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $refundReference;
    protected int $orderId;
    protected int $amountCents;
    protected string $reason;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $refundReference,
        int $orderId,
        int $amountCents,
        string $reason = 'Customer refund request'
    ) {
        $this->refundReference = $refundReference;
        $this->orderId = $orderId;
        $this->amountCents = $amountCents;
        $this->reason = $reason;
    }

    /**
     * Execute the job.
     */
    public function handle(KpiService $kpiService, LeaderboardService $leaderboardService): void
    {
        try {
            // Idempotency check - if refund already exists and processed, skip
            $existingRefund = Refund::findByReference($this->refundReference);
            if ($existingRefund && $existingRefund->status === Refund::STATUS_PROCESSED) {
                Log::info("Refund {$this->refundReference} already processed, skipping");
                return;
            }

            // If refund exists but failed/pending, we can retry
            if ($existingRefund && $existingRefund->status === Refund::STATUS_FAILED) {
                Log::info("Retrying failed refund {$this->refundReference}");
            }

            $order = Order::findOrFail($this->orderId);

            // Validate refund can be processed
            if (!Refund::canRefundOrder($order, $this->amountCents)) {
                throw new \InvalidArgumentException(
                    "Cannot refund {$this->amountCents} cents for order {$order->id}. " .
                    "Total order: {$order->total_cents}, Already refunded: " . 
                    Refund::getTotalRefundedForOrder($order->id)
                );
            }

            DB::transaction(function () use ($order, $existingRefund, $kpiService, $leaderboardService) {
                // Create or update refund record
                if ($existingRefund) {
                    $refund = $existingRefund;
                    $refund->update([
                        'status' => Refund::STATUS_PENDING,
                        'failure_reason' => null
                    ]);
                } else {
                    $refund = Refund::create([
                        'order_id' => $this->orderId,
                        'refund_reference' => $this->refundReference,
                        'amount_cents' => $this->amountCents,
                        'type' => $this->determineRefundType($order),
                        'status' => Refund::STATUS_PENDING,
                        'reason' => $this->reason,
                        'metadata' => [
                            'order_total_cents' => $order->total_cents,
                            'customer_id' => $order->customer_id,
                            'processed_by' => 'system'
                        ]
                    ]);
                }

                // Simulate refund processing (in real world, this would call payment gateway)
                $this->processRefundWithGateway($refund);

                // Mark as processed
                $refund->markAsProcessed();

                // Update analytics
                $this->updateAnalytics($order, $refund, $kpiService, $leaderboardService);

                Log::info("Refund processed successfully", [
                    'refund_id' => $refund->id,
                    'refund_reference' => $this->refundReference,
                    'order_id' => $this->orderId,
                    'amount_cents' => $this->amountCents,
                    'type' => $refund->type
                ]);
            });

        } catch (\Throwable $e) {
            // Mark refund as failed
            if (isset($refund)) {
                $refund->markAsFailed($e->getMessage());
            } elseif ($existingRefund) {
                $existingRefund->markAsFailed($e->getMessage());
            }

            Log::error("Refund processing failed", [
                'refund_reference' => $this->refundReference,
                'order_id' => $this->orderId,
                'amount_cents' => $this->amountCents,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Determine if this is a full or partial refund
     */
    private function determineRefundType(Order $order): string
    {
        $totalRefunded = Refund::getTotalRefundedForOrder($order->id);
        $totalAfterRefund = $totalRefunded + $this->amountCents;

        return $totalAfterRefund >= $order->total_cents 
            ? Refund::TYPE_FULL 
            : Refund::TYPE_PARTIAL;
    }

    /**
     * Simulate refund processing with payment gateway
     */
    private function processRefundWithGateway(Refund $refund): void
    {
        // In a real implementation, this would call the payment gateway
        // For simulation, we'll just add a small delay and log
        
        Log::info("Processing refund with payment gateway", [
            'refund_id' => $refund->id,
            'amount_cents' => $refund->amount_cents,
            'gateway_ref' => $refund->refund_reference
        ]);

        // Simulate processing time
        usleep(100000); // 0.1 seconds

        // Simulate occasional gateway failures (5% chance)
        if (rand(1, 100) <= 5) {
            throw new \RuntimeException("Payment gateway error: Refund processing failed");
        }
    }

    /**
     * Update KPIs and leaderboard analytics
     */
    private function updateAnalytics(
        Order $order, 
        Refund $refund, 
        KpiService $kpiService, 
        LeaderboardService $leaderboardService
    ): void {
        $today = now()->format('Y-m-d');

        // Update KPIs - subtract refunded amount from revenue
        $kpiService->trackRefund($today, $refund->amount_cents);

        // Update leaderboard - reduce customer score for refund
        $leaderboardService->adjustCustomerScoreForRefund($order->customer_id, $refund->amount_cents);
    }
}
