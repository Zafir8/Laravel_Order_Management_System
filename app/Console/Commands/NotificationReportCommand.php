<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotificationReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:report {--limit=10 : Number of notifications to show} {--type= : Filter by notification type} {--status= : Filter by status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show notification history report';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $type = $this->option('type');
        $status = $this->option('status');

        $query = DB::table('notifications')
            ->join('orders', 'notifications.order_id', '=', 'orders.id')
            ->select([
                'notifications.id',
                'notifications.type',
                'notifications.status',
                'notifications.channel',
                'orders.external_ref',
                'notifications.customer_id',
                'notifications.total_cents',
                'notifications.sent_at',
                'notifications.created_at'
            ])
            ->orderBy('notifications.created_at', 'desc');

        if ($type) {
            $query->where('notifications.type', $type);
        }

        if ($status) {
            $query->where('notifications.status', $status);
        }

        $notifications = $query->limit($limit)->get();

        if ($notifications->isEmpty()) {
            $this->warn('No notifications found.');
            return 0;
        }

        $this->info("📢 Notification Report (Last {$limit} notifications)");
        $this->newLine();

        $tableData = [];
        foreach ($notifications as $notification) {
            $tableData[] = [
                $notification->id,
                $notification->external_ref,
                $notification->type,
                $notification->status,
                $notification->channel,
                '$' . number_format($notification->total_cents / 100, 2),
                $notification->sent_at ?: 'Not sent',
                $notification->created_at
            ];
        }

        $this->table([
            'ID',
            'Order',
            'Type',
            'Status',
            'Channel',
            'Total',
            'Sent At',
            'Created At'
        ], $tableData);

        // Summary
        $totalCount = DB::table('notifications')->count();
        $successCount = DB::table('notifications')->where('status', 'sent')->count();
        $failedCount = DB::table('notifications')->where('status', 'failed')->count();
        
        $this->newLine();
        $this->info("📊 Summary:");
        $this->line("Total notifications: {$totalCount}");
        $this->line("Sent successfully: {$successCount}");
        $this->line("Failed: {$failedCount}");

        return 0;
    }
}
