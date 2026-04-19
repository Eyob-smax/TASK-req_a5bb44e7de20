<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FeeCategory;
use App\Models\TaxRule;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

final class TaxRuleService
{
    public function __construct(
        private readonly AuditLogger $audit,
    ) {
    }

    public function create(User $actor, FeeCategory $category, array $data): TaxRule
    {
        return DB::transaction(function () use ($actor, $category, $data): TaxRule {
            $rule = TaxRule::create([
                'fee_category_id' => $category->id,
                'rate_bps'        => $data['rate_bps'],
                'effective_from'  => $data['effective_from'],
                'effective_to'    => $data['effective_to'] ?? null,
            ]);

            $this->audit->record($actor->id, 'tax_rule.created', 'tax_rule', $rule->id, [
                'fee_category_id' => $category->id,
                'rate_bps'        => $rule->rate_bps,
            ]);

            return $rule;
        });
    }

    public function update(User $actor, TaxRule $rule, array $data): TaxRule
    {
        return DB::transaction(function () use ($actor, $rule, $data): TaxRule {
            $rule->update(array_intersect_key($data, array_flip(['rate_bps', 'effective_from', 'effective_to'])));

            $this->audit->record($actor->id, 'tax_rule.updated', 'tax_rule', $rule->id, [
                'changes' => $data,
            ]);

            return $rule->fresh();
        });
    }
}
