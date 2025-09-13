<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Order $order;
    protected string $notificationType;
    protected string $channel;
    protected ?string $failureReason;

    /**
     * Create a new job instance.
     */
    public function __construct(
        Order $order,
        string $notificationType,
        string $channel = Notification::CHANNEL_LOG,
        ?string $failureReason = null
    ) {
        $this->order = $order;
        $this->notificationType = $notificationType;
        $this->channel = $channel;
        $this->failureReason = $failureReason;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Create notification record
            $notification = Notification::create([
                'order_id' => $this->order->id,
                'customer_id' => $this->order->customer_id,
                'type' => $this->notificationType,
                'status' => Notification::STATUS_PENDING,
                'total_cents' => $this->order->total_cents,
                'channel' => $this->channel,
                'data' => [
                    'order_external_ref' => $this->order->external_ref,
                    'failure_reason' => $this->failureReason,
                    'payment_ref' => $this->order->payment_ref,
                ]
            ]);

            // Send notification based on channel
            switch ($this->channel) {
                case Notification::CHANNEL_EMAIL:
                    $this->sendEmailNotification($notification);
                    break;
                    
                case Notification::CHANNEL_LOG:
                    $this->sendLogNotification($notification);
                    break;
                    
                case Notification::CHANNEL_SMS:
                    $this->sendSmsNotification($notification);
                    break;
                    
                default:
                    throw new \InvalidArgumentException("Unsupported notification channel: {$this->channel}");
            }

            // Mark as sent
            $notification->markAsSent();

        } catch (\Throwable $e) {
            // Mark as failed
            if (isset($notification)) {
                $notification->markAsFailed($e->getMessage());
            }
            
            Log::error("Failed to send notification for order {$this->order->id}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Send email notification
     */
    private function sendEmailNotification(Notification $notification): void
    {
        // For now, we'll just log it as email would require mail configuration
        $subject = $this->getNotificationSubject();
        $message = $this->getNotificationMessage($notification);
        
        Log::info("EMAIL NOTIFICATION - {$subject}", [
            'to' => $this->order->customer->email ?? 'customer@example.com',
            'subject' => $subject,
            'message' => $message,
            'order_id' => $this->order->id,
            'notification_id' => $notification->id
        ]);

        // In a real implementation, you would send actual email here:
        // Mail::to($this->order->customer->email)->send(new OrderNotificationMail($notification));
    }

    /**
     * Send log notification
     */
    private function sendLogNotification(Notification $notification): void
    {
        $message = $this->getNotificationMessage($notification);
        
        Log::info("ORDER NOTIFICATION", [
            'type' => $this->notificationType,
            'order_id' => $this->order->id,
            'customer_id' => $this->order->customer_id,
            'external_ref' => $this->order->external_ref,
            'total_cents' => $this->order->total_cents,
            'status' => $this->order->status,
            'message' => $message,
            'notification_id' => $notification->id
        ]);
    }

    /**
     * Send SMS notification
     */
    private function sendSmsNotification(Notification $notification): void
    {
        // For now, we'll just log it as SMS would require SMS service configuration
        $message = $this->getNotificationMessage($notification);
        
        Log::info("SMS NOTIFICATION", [
            'to' => $this->order->customer->phone ?? '+1234567890',
            'message' => $message,
            'order_id' => $this->order->id,
            'notification_id' => $notification->id
        ]);

        // In a real implementation, you would send actual SMS here:
        // SMSService::send($this->order->customer->phone, $message);
    }

    /**
     * Get notification subject
     */
    private function getNotificationSubject(): string
    {
        return match ($this->notificationType) {
            Notification::TYPE_ORDER_SUCCESS => "Order {$this->order->external_ref} - Successfully Processed",
            Notification::TYPE_ORDER_FAILURE => "Order {$this->order->external_ref} - Processing Failed",
            Notification::TYPE_PAYMENT_SUCCESS => "Order {$this->order->external_ref} - Payment Successful",
            Notification::TYPE_PAYMENT_FAILURE => "Order {$this->order->external_ref} - Payment Failed",
            default => "Order {$this->order->external_ref} - Update"
        };
    }

    /**
     * Get notification message
     */
    private function getNotificationMessage(Notification $notification): string
    {
        $baseMessage = "Order #{$this->order->external_ref} for customer {$this->order->customer_id} ";
        $baseMessage .= "with total $" . number_format($this->order->total_cents / 100, 2);

        return match ($this->notificationType) {
            Notification::TYPE_ORDER_SUCCESS => $baseMessage . " has been successfully processed and finalized.",
            Notification::TYPE_ORDER_FAILURE => $baseMessage . " failed to process. Reason: " . ($this->failureReason ?? 'Unknown error'),
            Notification::TYPE_PAYMENT_SUCCESS => $baseMessage . " payment has been processed successfully.",
            Notification::TYPE_PAYMENT_FAILURE => $baseMessage . " payment failed. Reason: " . ($this->failureReason ?? 'Payment processing error'),
            default => $baseMessage . " has been updated."
        };
    }
}
