<?php

namespace App\Import\Parsers;

use App\Import\Contracts\ParserInterface;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\XLSX\Reader;

/**
 * XLSX файл-парсер с использованием библиотеки Spout.
 */
class XlsxParser extends AbstractExcelParser implements ParserInterface
{
    /**
     * Парсит XLSX-файл построчно, пропускает заголовок и пустые строки.
     * Возвращает генератор с ключом — индексом строки (1-based) и значением — массивом ячеек.
     *
     * @param int|null $limit Ограничение по количеству возвращаемых строк (без заголовка), если нужно.
     * @return \Generator<int, array>
     */
    public function parse(int $limit = null): \Generator
    {
        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($this->filePath);

        $validRowNumber = 0;
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $cells = $row->toArray();

                // Пропускаем полностью пустые
                if (empty(array_filter($cells, fn($cell) => trim((string) $cell) !== ''))) {
                    continue;
                }

                // Пропускаем заголовок (первую непустую строку)
                if ($validRowNumber === 0) {
                    $validRowNumber++;
                    continue;
                }

                $validRowNumber++;
                yield $validRowNumber => $cells;

                if ($limit !== null && $validRowNumber >= $limit) {
                    break 2;
                }
            }
        }
        $reader->close();
    }
}
