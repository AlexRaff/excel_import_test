<?php

namespace Tests\Unit;

use App\Import\Dto\ImportRowDto;
use App\Import\Events\ImportItemProcessed;
use App\Import\Jobs\ImportChunkJob;
use App\Models\ImportItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class ImportChunkJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * добавил альтернативное определение теста test_ в начале без #[Test] для разнообразия
     */
    public function test_handle_inserts_unique_items_and_dispatches_event_and_increments_redis()
    {
        Event::fake();
        Redis::shouldReceive('incrby')->once()->with('progress_key', 2);

        $chunk = [
            new ImportRowDto(1, 'Name1', '2023-01-01'),
            new ImportRowDto(2, 'Name2', '2023-01-01'),
        ];

        $job = new ImportChunkJob($chunk, 'progress_key');
        $job->handle();

        $this->assertDatabaseHas('import_items', ['id' => 1, 'name' => 'Name1']);
        $this->assertDatabaseHas('import_items', ['id' => 2, 'name' => 'Name2']);

        Event::assertDispatched(ImportItemProcessed::class, function ($event) {
            return count($event->items) === 2
                && $event->items[0]['id'] === 1
                && $event->items[1]['id'] === 2;
        });
    }

    public function test_handle_skips_duplicates_and_logs_errors()
    {
        ImportItem::create(['id' => 1, 'name' => 'Exist', 'date' => '2023-01-01']);

        Event::fake();
        Redis::shouldReceive('incrby')->once()->with('progress_key', 1);

        $chunk = [
            new ImportRowDto(1, 'Duplicate', '2023-01-01'), //дубликат
            new ImportRowDto(2, 'Name2', '2023-01-01'),
            new ImportRowDto(null, 'NoID', '2023-01-01'), //пропускается
        ];

        $job = new ImportChunkJob($chunk, 'progress_key');
        $job->handle();

        $this->assertDatabaseMissing('import_items', ['name' => 'Duplicate']);
        $this->assertDatabaseHas('import_items', ['id' => 2]);

        Event::assertDispatched(ImportItemProcessed::class, function ($event) {
            return count($event->items) === 1 && $event->items[0]['id'] === 2;
        });
    }
}
