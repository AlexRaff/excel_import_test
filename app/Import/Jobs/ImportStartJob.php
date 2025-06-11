<?php

namespace App\Import\Jobs;

use App\Import\Parsers\XlsxParser;
use App\Import\Services\ImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportStartJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $path;
    public string $progressKey;

    public function __construct(string $path, string $progressKey)
    {
        $this->path = $path;
        $this->progressKey = $progressKey;
    }

    public function handle(): void
    {
        $parser = new XlsxParser($this->path);

        $service = new ImportService($parser);
        $service->import($this->progressKey);
    }
}
