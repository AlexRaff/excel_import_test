<?php

namespace App\Import\Contracts;

interface ParserInterface
{
    public function parse(int $limit): \Generator;
}
