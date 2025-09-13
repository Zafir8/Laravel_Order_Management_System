<?php

namespace App\Jobs;

use App\Services\OrderWorkflowService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPaymentCallbackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $paymentRef;
    protected bool $success;
    protected ?string $reason;

    /**
     * Create a new job instance.
     *
     * @param string $paymentRef
     * @param bool $success
     * @param string|null $reason
     */
    public function __construct(string $paymentRef, bool $success, ?string $reason = null)
    {
        $this->paymentRef = $paymentRef;
        $this->success = $success;
        $this->reason = $reason;
    }

    /**
     * Execute the job.
     */
    public function handle(OrderWorkflowService $workflowService): void
    {
        try {
            $workflowService->handlePaymentCallback(
                $this->paymentRef,
                $this->success,
                $this->reason
            );
        } catch (\Throwable $e) {
            Log::error("Payment callback failed for {$this->paymentRef}: {$e->getMessage()}");
            throw $e;
        }
    }
}
