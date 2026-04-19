<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CatalogItem;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class CatalogService
{
    public function __construct(
        private readonly AuditLogger $audit,
    ) {
    }

    public function list(bool $activeOnly = true, int $perPage = 20): LengthAwarePaginator
    {
        $query = CatalogItem::with('feeCategory')->orderBy('name');
        if ($activeOnly) {
            $query->where('is_active', true);
        }
        return $query->paginate($perPage);
    }

    public function create(User $actor, array $data): CatalogItem
    {
        return DB::transaction(function () use ($actor, $data): CatalogItem {
            $item = CatalogItem::create([
                'fee_category_id'  => $data['fee_category_id'],
                'sku'              => $data['sku'],
                'name'             => $data['name'],
                'description'      => $data['description'] ?? null,
                'unit_price_cents' => $data['unit_price_cents'],
                'is_active'        => $data['is_active'] ?? true,
            ]);

            $this->audit->record($actor->id, 'catalog_item.created', 'catalog_item', $item->id, [
                'sku'  => $item->sku,
                'name' => $item->name,
            ]);

            return $item->load('feeCategory');
        });
    }

    public function update(User $actor, CatalogItem $item, array $data): CatalogItem
    {
        return DB::transaction(function () use ($actor, $item, $data): CatalogItem {
            $item->update(array_intersect_key($data, array_flip([
                'fee_category_id', 'sku', 'name', 'description', 'unit_price_cents', 'is_active',
            ])));

            $this->audit->record($actor->id, 'catalog_item.updated', 'catalog_item', $item->id, [
                'changes' => $data,
            ]);

            return $item->fresh('feeCategory');
        });
    }
}
