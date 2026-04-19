<?php

namespace Database\Factories;

use App\Enums\SensitiveWordMatchType;
use App\Models\SensitiveWordRule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SensitiveWordRule>
 */
class SensitiveWordRuleFactory extends Factory
{
    protected $model = SensitiveWordRule::class;

    public function definition(): array
    {
        return [
            'pattern'    => fake()->unique()->word(),
            'match_type' => SensitiveWordMatchType::Substring,
            'is_active'  => true,
            'created_by' => User::factory(),
        ];
    }

    public function exact(): static
    {
        return $this->state(['match_type' => SensitiveWordMatchType::Exact]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
