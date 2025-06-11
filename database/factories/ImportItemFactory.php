<?php

namespace Database\Factories;

use App\Models\ImportItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImportItemFactory extends Factory
{
    protected $model = ImportItem::class;

    public function definition()
    {
        return [
            'id' => $this->faker->unique()->numberBetween(1, 100000),
            'name' => $this->faker->name,
            'date' => $this->faker->date(),
        ];
    }
}
