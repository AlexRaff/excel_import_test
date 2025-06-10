<?php

namespace App\Import\Validators;

/**
 * Результат валидации одной строки.
 */
class ValidationResult
{
    protected int $line;
    protected array $errors;

    public function __construct(int $line, array $errors)
    {
        $this->line = $line;
        $this->errors = $errors;
    }

    /**
     * @return bool
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * @return bool
     */
    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * @return array|string[]
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * В одну строку, для записи в файл: "23 - Ошибка1, Ошибка2"
     * @return string
     */
    public function __toString(): string
    {
        if ($this->passes()) {
            return '';
        }
        return $this->line . ' - ' . implode(', ', $this->errors);
    }
}
