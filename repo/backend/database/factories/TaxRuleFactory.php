<?php

namespace Database\Factories;

use App\Models\FeeCategory;
use App\Models\TaxRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaxRule>
 */
class TaxRuleFactory extends Factory
{
    protected $model = TaxRule::class;

    public function definition(): array
    {
        return [
            'fee_category_id' => FeeCategory::factory(),
            'rate_bps'        => 1500,
            'effective_from'  => now()->subYear()->format('Y-m-d'),
            'effective_to'    => null,
        ];
    }
}
