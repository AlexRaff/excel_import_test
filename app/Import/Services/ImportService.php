<?php

namespace App\Import\Services;

use App\Import\Contracts\ParserInterface;
use App\Import\Jobs\ImportChunkJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ImportService
{
    protected ParserInterface $parser;

    public function __construct(ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    public function import(string $progressKey): void
    {
        $chunk = $lastChunk = [];
        $chunkSize = 500;
        $line = 0;

        foreach ($this->parser->parse() as $index => $cells) {
            if (empty(trim($cells[0] ?? ''))) {
                continue; // не добавляем пустую строку в чанк и не считаем прогресс
            }

            $lastChunk = [
                'line' => $index,
                'data' => [
                    'id' => $cells[0] ?? null,
                    'name' => $cells[1] ?? null,
                    'date' => $cells[2] ?? null,
                ],
            ];

            $chunk[] = $lastChunk;
            $line++;

            Log::channel('import')->info('Chunk: ', $lastChunk);
            if (count($chunk) >= $chunkSize) {
                ImportChunkJob::dispatch($chunk, $progressKey);
                $chunk = [];
            }

            Redis::set($progressKey, $line);
        }

        if (!empty($chunk)) {
            ImportChunkJob::dispatch($chunk, $progressKey);
        }
    }
}
