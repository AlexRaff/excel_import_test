<?php

namespace Tests\Unit;

use App\Import\Events\ImportItemProcessed;
use PHPUnit\Framework\TestCase;

class ImportItemProcessedTest extends TestCase
{
    public function test_event_contains_items()
    {
        $items = [
            ['id' => 1, 'name' => 'Test', 'date' => '2023-01-01'],
        ];

        $event = new ImportItemProcessed($items);

        $this->assertEquals($items, $event->items);
    }
}
