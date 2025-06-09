<?php

namespace App\Import\Validators;

use Illuminate\Support\Facades\Validator;

class ExcelRowValidator
{
    protected array $data;
    protected int $line;
    protected $validator;

    public function __construct(array $data, int $line)
    {
        $this->data = $data;
        $this->line = $line;
        $this->validator = $this->makeValidator();
    }

    protected function makeValidator()
    {
        return Validator::make([
            'id' => $this->data[0] ?? null,
            'name' => $this->data[1] ?? null,
            'date' => $this->data[2] ?? null,
        ], [
            'id' => 'required|integer',
            'name' => 'required|string',
            'date' => 'required|date_format:d.m.Y',
        ]);
    }

    public function fails(): bool
    {
        return $this->validator->fails();
    }

    public function errorMessage(): string
    {
        $errors = $this->validator->errors()->all();
        return "{$this->line} - " . implode(', ', $errors);
    }

    public function errors(): array
    {
        return $this->validator->errors()->all();
    }
}
