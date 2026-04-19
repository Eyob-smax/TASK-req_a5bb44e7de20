<?php

namespace Database\Factories;

use App\Models\CatalogItem;
use App\Models\Order;
use App\Models\OrderLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderLine>
 */
class OrderLineFactory extends Factory
{
    protected $model = OrderLine::class;

    public function definition(): array
    {
        $qty  = 1;
        $unit = fake()->numberBetween(1000, 50000);

        return [
            'order_id'          => Order::factory(),
            'catalog_item_id'   => CatalogItem::factory(),
            'quantity'          => $qty,
            'unit_price_cents'  => $unit,
            'tax_rule_snapshot' => ['rate_bps' => 1500, 'effective_from' => now()->subYear()->format('Y-m-d'), 'effective_to' => null],
            'line_total_cents'  => $qty * $unit,
        ];
    }
}
