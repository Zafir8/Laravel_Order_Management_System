<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class CheckKpiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kpi:check {--date= : The date to check KPIs for (Y-m-d format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check daily KPI metrics for a specific date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->option('date') ?? now()->format('Y-m-d');
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $this->error('Invalid date format. Please use Y-m-d format (e.g., 2025-09-13)');
            return 1;
        }

        $key = "kpi:daily:{$date}";
        $kpis = Redis::hgetall($key);

        if (empty($kpis)) {
            $this->warn("No KPI data found for {$date}");
            return 0;
        }

        $this->info("📊 KPI Report for {$date}");
        $this->newLine();

        // Display metrics
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Orders', number_format($kpis['order_count'] ?? 0)],
                ['Revenue (cents)', number_format($kpis['revenue_cents'] ?? 0)],
                ['Revenue ($)', '$' . number_format(($kpis['revenue_cents'] ?? 0) / 100, 2)],
            ]
        );

        return 0;
    }
}
