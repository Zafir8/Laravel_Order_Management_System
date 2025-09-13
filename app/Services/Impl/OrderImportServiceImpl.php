<?php

namespace App\Services\Impl;

use App\Services\OrderImportService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;


class OrderImportServiceImpl implements OrderImportService
{
    /**
     * Queue a large CSV for import.
     *
     * @param string $csvPath
     * @return string
     */
    public function queueImport(string $csvPath): string
    {
        $fullPath = $this->resolvePath($csvPath);

        if (!is_readable($fullPath)) {
            throw new \InvalidArgumentException("CSV file not readable: {$csvPath}");
        }

        $batchId = 'orders:import:' . Str::uuid()->toString();

        // Dispatch a job to process the CSV (you will implement this job later)
        Bus::dispatch(new \App\Jobs\ImportCsvStreamJob($fullPath, $batchId));

        return $batchId;
    }

    /**
     * @param string $input
     * @return string
     */
    private function resolvePath(string $input): string
    {
        if (is_file($input)) {
            return realpath($input);
        }

        
        $local = storage_path("app/{$input}");
        if (is_file($local)) {
            return realpath($local);
        }

        return $input;
    }
}
