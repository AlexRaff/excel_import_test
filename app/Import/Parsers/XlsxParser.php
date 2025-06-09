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
     * Парсит XLSX-файл построчно и вызывает callback на каждую строку (кроме заголовка).
     *
     * @param callable $callback Функция вида function (int $rowIndex, array $cells)
     */
    public function parse(callable $callback): void
    {
        /** @var Reader $reader */
        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($this->filePath);

        foreach ($reader->getSheetIterator() as $sheet) {
            $rowIndex = 0;
            foreach ($sheet->getRowIterator() as $row) {
                $rowIndex++; // 1-based индекс строки

                if ($rowIndex === 1) {
                    continue; // Пропустить заголовок
                }

                $cells = $row->toArray();

                // Пропустить полностью пустые строки
                if (empty(array_filter($cells, fn($cell) => trim((string) $cell) !== ''))) {
                    continue;
                }

                $callback($rowIndex, $cells);
            }
        }

        $reader->close();
    }
}
