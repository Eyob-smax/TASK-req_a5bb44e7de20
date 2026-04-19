<?php

namespace Database\Factories;

use App\Models\FeeCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeeCategory>
 */
class FeeCategoryFactory extends Factory
{
    protected $model = FeeCategory::class;

    public function definition(): array
    {
        return [
            'code'       => strtolower(fake()->unique()->bothify('cat_###')),
            'label'      => fake()->words(2, true),
            'is_taxable' => true,
        ];
    }

    public function nontaxable(): static
    {
        return $this->state(['is_taxable' => false]);
    }
}
