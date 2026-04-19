<?php

declare(strict_types=1);

use App\Enums\AccountStatus;
use App\Enums\OrderStatus;
use App\Models\CatalogItem;
use App\Models\FeeCategory;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

test('artisan auto-close command closes overdue orders', function () {
    $user     = User::factory()->create(['status' => AccountStatus::Active]);
    $category = FeeCategory::factory()->create(['is_taxable' => false]);
    $item     = CatalogItem::factory()->for($category)->create(['unit_price_cents' => 100, 'is_active' => true]);

    $service = app(OrderService::class);
    $order   = $service->create($user, [['catalog_item_id' => $item->id, 'quantity' => 1]]);
    $order->update(['auto_close_at' => now()->subMinutes(31)]);

    Artisan::call('campuslearn:orders:auto-close');

    expect($order->fresh()->status)->toBe(OrderStatus::Canceled);
});
