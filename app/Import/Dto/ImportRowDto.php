<?php

namespace App\Import\Dto;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Illuminate\Support\Facades\Log;
use JsonSerializable;

class ImportRowDto implements JsonSerializable
{
    public ?int $id;
    public ?string $name;
    public ?DateTimeInterface $date;

    /**
     * @param int|string|null $id
     * @param string|null $name
     * @param string|null $dateStr
     */
    public function __construct(int|string|null $id, ?string $name, ?string $dateStr)
    {
        $this->id = $id !== null ? (int)$id : null;
        $this->name = $name;
        $this->date = $this->parseDate($dateStr);
    }

    /**
     * Пытается распарсить строку даты с поддержкой различных форматов.
     *
     * @param string|null $dateStr
     * @return DateTimeInterface|null
     */
    private function parseDate(?string $dateStr): ?DateTimeInterface
    {
        if (!$dateStr) {
            return null;
        }

        $formats = ['d.m.Y', 'Y-m-d', 'd/m/Y', 'm-d-Y', 'd-m-Y', 'Y/m/d'];

        foreach ($formats as $format) {
            $dt = DateTimeImmutable::createFromFormat($format, $dateStr);
            if ($dt !== false) {
                return $dt;
            }
        }

        // fallback на strtotime
        try {
            $timestamp = strtotime($dateStr);
            if ($timestamp !== false) {
                return (new DateTimeImmutable())->setTimestamp($timestamp);
            }
        } catch (Exception $e) {
            // Игнорируем, запишем в лог ниже
        }

        Log::error('Ошибка преобразования даты в DTO', [
            'dateStr' => $dateStr,
        ]);

        return null;
    }

    /**
     * Форматирует дату в строку "Y-m-d" для вставки в БД
     */
    public function getFormattedDate(): ?string
    {
        return $this->date?->format('Y-m-d');
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'date' => $this->getFormattedDate(),
        ];
    }
}
