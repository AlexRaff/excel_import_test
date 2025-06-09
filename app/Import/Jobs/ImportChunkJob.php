<?php

namespace App\Import\Jobs;


use App\Import\Events\ImportItemProcessed;
use App\Models\ImportItem;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ImportChunkJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    protected array $chunk;
    public ?string $errorFilePath;
    protected string $progressKey;

    public function __construct(array $chunk, $progressKey, ?string $errorFilePath = null)
    {
        $this->chunk = $chunk;
        $this->progressKey = $progressKey;
        $this->errorFilePath = $errorFilePath;
    }

    public function handle()
    {
        $ids = array_filter(array_map(fn($row) => $row['data']['id'] ?? null, $this->chunk));
        $existingIds = ImportItem::whereIn('id', $ids)->pluck('id')->all();

        $toInsert = [];

        foreach ($this->chunk as $row) {
            $id = $row['data']['id'] ?? null;
            if ($id === null) {
                // Можно логировать ошибки, если файл указан
                if ($this->errorFilePath) {
                    file_put_contents($this->errorFilePath, "Line {$row['line']}: missing id\n", FILE_APPEND);
                }
                continue;
            }

            if (in_array($id, $existingIds)) {
                if ($this->errorFilePath) {
                    file_put_contents($this->errorFilePath, json_encode($row) . PHP_EOL, FILE_APPEND);
                }
            } else {
                $toInsert[] = [
                    'id' => $id,
                    'name' => $row['data']['name'] ?? null,
                    'date' => $row['data']['date'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                event(new ImportItemProcessed(
                    $id,
                    $row['data']['name'],
                    $row['data']['date']
                ));
            }
        }

        if ($toInsert) {
            ImportItem::insert($toInsert);
            Redis::incrby($this->progressKey, count($toInsert));
        }
    }
}
