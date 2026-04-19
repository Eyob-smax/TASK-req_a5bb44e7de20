<?php

namespace Database\Seeders;

use App\Models\RefundReasonCode;
use Illuminate\Database\Seeder;

class RefundReasonCodeSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['code' => 'duplicate', 'label' => 'Duplicate charge', 'is_active' => true],
            ['code' => 'service_not_rendered', 'label' => 'Service not rendered', 'is_active' => true],
            ['code' => 'admin_adjustment', 'label' => 'Administrative adjustment', 'is_active' => true],
            ['code' => 'waiver_issued', 'label' => 'Waiver issued', 'is_active' => true],
        ];

        foreach ($rows as $row) {
            RefundReasonCode::updateOrCreate(['code' => $row['code']], $row);
        }
    }
}
