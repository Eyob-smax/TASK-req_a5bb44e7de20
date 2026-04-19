<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FeeCategory;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class FeeCategoryService
{
    public function __construct(
        private readonly AuditLogger $audit,
    ) {
    }

    public function list(): Collection
    {
        return FeeCategory::with('taxRules')->orderBy('code')->get();
    }

    public function create(User $actor, array $data): FeeCategory
    {
        return DB::transaction(function () use ($actor, $data): FeeCategory {
            $category = FeeCategory::create([
                'code'       => $data['code'],
                'label'      => $data['label'],
                'is_taxable' => $data['is_taxable'] ?? false,
            ]);

            $this->audit->record($actor->id, 'fee_category.created', 'fee_category', $category->id, [
                'code' => $category->code,
            ]);

            return $category;
        });
    }

    public function update(User $actor, FeeCategory $category, array $data): FeeCategory
    {
        return DB::transaction(function () use ($actor, $category, $data): FeeCategory {
            $category->update(array_intersect_key($data, array_flip(['code', 'label', 'is_taxable'])));

            $this->audit->record($actor->id, 'fee_category.updated', 'fee_category', $category->id, [
                'changes' => $data,
            ]);

            return $category->fresh('taxRules');
        });
    }
}
