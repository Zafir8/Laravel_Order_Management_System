<?php

namespace App\Jobs;

use App\Jobs\UpsertAndProcessOrderJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

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

            // Dispatch a job to handle this order
            UpsertAndProcessOrderJob::dispatch($data, $this->batchId);
        }

        fclose($handle);
    }
}
