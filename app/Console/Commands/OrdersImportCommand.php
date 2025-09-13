<?php

namespace App\Console\Commands;

use App\Services\OrderImportService;
use Illuminate\Console\Command;

class OrdersImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Usage:
     *   php artisan orders:import storage/app/orders.csv
     *
     * @var string
     */
    protected $signature = 'orders:import {file : Path to the CSV file to import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a large CSV of orders into the system (queued)';

    /**
     * Execute the console command.
     */
    public function handle(OrderImportService $importService): int
    {
        $file = $this->argument('file');

        try {
            $batchId = $importService->queueImport($file);
            $this->info("Import queued successfully for {$file}");
            $this->line("Batch ID: {$batchId}");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Failed to queue import: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
