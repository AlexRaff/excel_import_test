<?php

namespace App\Import\Services;

use App\Import\Contracts\ParserInterface;
use App\Import\Dto\ImportRowDto;
use App\Import\Events\ImportFinished;
use App\Import\Jobs\ImportChunkJob;
use App\Import\Validators\ExcelRowValidator;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class ImportService
{
    protected ParserInterface $parser;
    protected int $chunkSize = 50;

    /**
     * Интервал в секундах, через который обновляется прогресс в REDIS
     */
    private const REDIS_REFRESH_RATE = 1;

    /**
     * @param ParserInterface $parser
     * @param int $chunkSize
     */
    public function __construct(ParserInterface $parser, int $chunkSize = 50)
    {
        $this->parser = $parser;
        $this->chunkSize = $chunkSize;
    }

    /**
     * @param string $progressKey
     * @return void
     */
    public function import(string $progressKey): void
    {
        $start = microtime(true); // старт таймера

        $lastProgressWrite = time();

        $chunk = [];
        $line = 0;
        $errors = [];

        foreach ($this->parser->parse() as $index => $cells) {
            // Пропускаем пустые строки (например, если пуст первый столбец)
            if (empty(trim($cells[0] ?? ''))) {
                continue;
            }

            // Валидируем строку через валидатор
            $validationResult = ExcelRowValidator::validate($cells, $index);

            if ($validationResult->fails()) {
                $errors[$index] = $validationResult->errors();
                continue;
            }

            // Создаём DTO
            $dto = new ImportRowDto(
                $cells[0] ?? null,
                $cells[1] ?? null,
                $cells[2] ?? null,
            );

            $chunk[] = $dto;
            $line++;

            if (count($chunk) >= $this->chunkSize) {
                ImportChunkJob::dispatch($chunk, $progressKey);
                $chunk = [];
            }

            if (time() - $lastProgressWrite >= self::REDIS_REFRESH_RATE) {
                Redis::set($progressKey, $line);
                $lastProgressWrite = time();
            }
        }

        // Финальная запись прогресса после цикла
        Redis::set($progressKey, $line);

        // Отправляем последний чанк, если есть
        if (!empty($chunk)) {
            ImportChunkJob::dispatch($chunk, $progressKey);
        }

        // Записываем все ошибки в result.txt
        $this->writeErrorsToFile($errors);

        $end = microtime(true); // конец таймера
        $duration = round($end - $start, 2);

        event(new ImportFinished($duration));
    }

    /**
     * Записывает ошибки в storage/app/result.txt
     * Формат: <номер строки> - <ошибка1>, <ошибка2>
     *
     * @param array<int, array<string>> $errors
     */
    protected function writeErrorsToFile(array $errors): void
    {
        if (empty($errors)) {
            return;
        }

        $lines = [];
        foreach ($errors as $lineNumber => $errs) {
            $lines[] = sprintf('%d - %s', $lineNumber, implode(', ', $errs));
        }

        $content = implode(PHP_EOL, $lines);

        Storage::disk('local')->put('result.txt', $content);
    }
}
