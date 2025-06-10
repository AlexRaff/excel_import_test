<?php

namespace App\Import\Validators;

use Illuminate\Support\Facades\Validator;

/**
 * Валидатор одной строки Excel (в формате [id, name, date]).
 */
class ExcelRowValidator
{
    /**
     * Основной метод: валидирует и возвращает ValidationResult.
     *
     * @param array<int,mixed> $row Массив ячеек: [0 => id, 1 => name, 2 => dateStr]
     * @param int $line Номер строки из файла (для отчёта об ошибках)
     * @return ValidationResult
     */
    public static function validate(array $row, int $line): ValidationResult
    {
        // Маппим под Laravel Validator
        $data = [
            'id' => $row[0] ?? null,
            'name' => $row[1] ?? null,
            'date' => $row[2] ?? null,
        ];

        $validator = Validator::make($data, static::rules());

        return new ValidationResult(
            $line,
            $validator->errors()->all()
        );
    }

    /**
     * Правила валидации полей.
     *
     * @return array<string,string|array>
     */
    protected static function rules(): array
    {
        return [
            'id' => ['required', 'integer'],
            'name' => ['required', 'string'],
            'date' => ['required', 'date_format:d.m.Y'],
        ];
    }
}
