<?php

namespace App\Import\Jobs;


use App\Import\Events\ImportItemProcessed;
use App\Models\ImportItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

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
            $date = null;

            if (!empty($row['data']['date'])) {
                try {
                    // Преобразуем из "дд.мм.гггг" в "гггг-мм-дд"
                    $date = \DateTime::createFromFormat('d.m.Y', $row['data']['date'])->format('Y-m-d');
                } catch (\Exception $e) {
                    // Логируем ошибку, но продолжаем обработку
                    Log::error('Ошибка преобразования даты', [
                        'original_date' => $row['data']['date'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $toInsert[] = [
                'id' => $row['data']['id'],
                'name' => $row['data']['name'] ?? null,
                'date' => $date,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            event(new ImportItemProcessed(
                $row['data']['id'],
                $row['data']['name'],
                $row['data']['date']
            ));
        }

        if ($toInsert) {
            ImportItem::insert($toInsert);
            Redis::incrby($this->progressKey, count($toInsert));
        }
    }
}
