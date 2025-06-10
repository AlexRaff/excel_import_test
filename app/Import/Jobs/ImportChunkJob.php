<?php

namespace App\Import\Jobs;

use App\Import\Dto\ImportRowDto;
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

    /**
     * @var ImportRowDto[]
     */
    protected array $chunk;

    protected string $progressKey;
    public ?string $errorFilePath;

    /**
     * @param ImportRowDto[] $chunk
     * @param string $progressKey
     * @param string|null $errorFilePath
     */
    public function __construct(array $chunk, string $progressKey, ?string $errorFilePath = null)
    {
        $this->chunk = $chunk;
        $this->progressKey = $progressKey;
        $this->errorFilePath = $errorFilePath;
    }

    public function handle(): void
    {
        if (empty($this->chunk)) {
            return;
        }

        // Собираем все ID для проверки дубликатов
        $ids = array_filter(array_map(fn(ImportRowDto $row) => $row->id, $this->chunk));

        // Существующие ID в БД
        $existingIds = ImportItem::whereIn('id', $ids)
            ->pluck('id')
            ->all();
        $existingIds = array_flip($existingIds);

        $toInsert = [];

        foreach ($this->chunk as $row) {
            // 1) Отфильтруем строки без ID
            if ($row->id === null) {
                $this->logError("Отсутствует ID, строка пропущена", $row);
                continue;
            }

            // 2) Отфильтруем дубликаты
            if (isset($existingIds[$row->id])) {
                $this->logError("Дубликат ID пропущен: {$row->id}", $row);
                continue;
            }

            // 3) Собираем данные для вставки
            $toInsert[] = [
                'id' => $row->id,
                'name' => $row->name,
                'date' => $row->getFormattedDate(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // 4) Сразу бросаем событие об успешной обработке строки
            event(new ImportItemProcessed(
                $row->id,
                $row->name,
                $row->date?->format('d.m.Y') ?? null
            ));
        }

        // 5) Вставка в базу и обновление прогресса
        if (!empty($toInsert)) {
            try {
                ImportItem::insert($toInsert);
                Redis::incrby($this->progressKey, count($toInsert));
            } catch (\Exception $e) {
                // На случай массового сбоя — логируем каждую запись
                foreach ($toInsert as $item) {
                    $this->logError(
                        "Ошибка вставки в базу: " . $e->getMessage(),
                        $item
                    );
                }
            }
        }
    }

    /**
     * Логируем сообщение об ошибке.
     *
     * @param string $message
     * @param ImportRowDto|array $rowData
     */
    protected function logError(string $message, ImportRowDto|array $rowData): void
    {
        // Если передан DTO — конвертим в массив
        if ($rowData instanceof ImportRowDto) {
            $rowData = $rowData->jsonSerialize();
        }

        $logMessage = $message
            . ' | Data: '
            . json_encode($rowData, JSON_UNESCAPED_UNICODE);

        if ($this->errorFilePath) {
            // LOCK_EX защищает от перекрёстных записей из параллельных процессов
            file_put_contents(
                $this->errorFilePath,
                $logMessage . PHP_EOL,
                FILE_APPEND | LOCK_EX
            );
        } else {
            Log::error($logMessage);
        }
    }
}
