<?php

namespace Database\Factories;

use App\Models\RefundReasonCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RefundReasonCode>
 */
class RefundReasonCodeFactory extends Factory
{
    protected $model = RefundReasonCode::class;

    public function definition(): array
    {
        return [
            'code'      => fake()->unique()->slug(2, false),
            'label'     => fake()->words(3, true),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
