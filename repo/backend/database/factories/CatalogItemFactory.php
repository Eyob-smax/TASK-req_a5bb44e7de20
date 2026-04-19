<?php

namespace Database\Factories;

use App\Models\CatalogItem;
use App\Models\FeeCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CatalogItem>
 */
class CatalogItemFactory extends Factory
{
    protected $model = CatalogItem::class;

    public function definition(): array
    {
        return [
            'fee_category_id'  => FeeCategory::factory(),
            'sku'              => strtoupper(fake()->unique()->bothify('SKU-?????')),
            'name'             => fake()->words(3, true),
            'description'      => fake()->sentence(),
            'unit_price_cents' => fake()->numberBetween(1000, 500000),
            'is_active'        => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
