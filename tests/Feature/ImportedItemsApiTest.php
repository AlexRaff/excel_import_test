<?php

namespace Tests\Feature;

use App\Models\ImportItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ImportedItemsApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_paginated_grouped_import_items()
    {
        ImportItem::factory()->create([
            'id' => 1,
            'name' => 'Item One',
            'date' => '2025-01-01',
        ]);
        ImportItem::factory()->create([
            'id' => 2,
            'name' => 'Item Two',
            'date' => '2025-01-01',
        ]);
        ImportItem::factory()->create([
            'id' => 3,
            'name' => 'Item Three',
            'date' => '2025-01-02',
        ]);

        $response = $this->getJson('/api/imported-items');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'date',
                    'items' => [
                        '*' => ['id', 'name', 'date', 'created_at', 'updated_at'],
                    ],
                ],
            ],
            'meta' => [
                'current_page',
                'last_page',
                'total',
            ],
        ]);

        $jsonData = $response->json('data');

        // Проверяем, что даты сгруппированы и правильное количество групп
        $this->assertCount(2, $jsonData);

        // Проверяем что первая группа — 2025-01-01 и в ней 2 элемента
        $this->assertEquals('2025-01-01', $jsonData[0]['date']);
        $this->assertCount(2, $jsonData[0]['items']);

        // Вторая группа — 2025-01-02 и 1 элемент
        $this->assertEquals('2025-01-02', $jsonData[1]['date']);
        $this->assertCount(1, $jsonData[1]['items']);
    }

    #[Test]
    public function it_filters_by_date_query_parameter()
    {
        ImportItem::factory()->create([
            'id' => 1,
            'name' => 'Item One',
            'date' => '2025-01-01',
        ]);
        ImportItem::factory()->create([
            'id' => 2,
            'name' => 'Item Two',
            'date' => '2025-01-02',
        ]);

        $response = $this->getJson('/api/imported-items?date=2025-01-01');

        $response->assertStatus(200);

        $jsonData = $response->json('data');

        $this->assertCount(1, $jsonData);
        $this->assertEquals('2025-01-01', $jsonData[0]['date']);
        $this->assertCount(1, $jsonData[0]['items']);
    }
}
