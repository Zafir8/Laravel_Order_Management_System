<?php

namespace App\Services;

/**
 * Service responsible for queuing the import of orders from a CSV file.
 */
interface OrderImportService
{
    /**
     * Queue a large CSV for import.
     *
     * @param string $csvPath  Path to the CSV file (absolute or relative to storage/app).
     * @return string          A batch ID or identifier for this import run.
     */
    public function queueImport(string $csvPath): string;
}
