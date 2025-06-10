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

        if ($dateStr) {
            try {
                $dt = DateTimeImmutable::createFromFormat('d.m.Y', $dateStr);
                if ($dt === false) {
                    throw new Exception("Invalid date format: {$dateStr}");
                }
                $this->date = $dt;
            } catch (Exception $e) {
                Log::error('Ошибка преобразования даты в DTO', [
                    'dateStr' => $dateStr,
                    'exception' => $e->getMessage(),
                ]);
                $this->date = null;
            }
        } else {
            $this->date = null;
        }
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
