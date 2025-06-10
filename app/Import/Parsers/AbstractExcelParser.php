<?php

namespace App\Import\Parsers;

use App\Import\Contracts\ParserInterface;

abstract class AbstractExcelParser implements ParserInterface
{
    protected string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    abstract public function parse(int $limit): \Generator;
}
