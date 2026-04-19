<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\BillLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillLine>
 */
class BillLineFactory extends Factory
{
    protected $model = BillLine::class;

    public function definition(): array
    {
        $qty  = 1;
        $unit = fake()->numberBetween(1000, 50000);

        return [
            'bill_id'           => Bill::factory(),
            'catalog_item_id'   => null,
            'description'       => fake()->sentence(4),
            'quantity'          => $qty,
            'unit_price_cents'  => $unit,
            'tax_rule_snapshot' => ['rate_bps' => 1500, 'effective_from' => now()->subYear()->format('Y-m-d'), 'effective_to' => null],
            'line_total_cents'  => $qty * $unit,
        ];
    }
}
