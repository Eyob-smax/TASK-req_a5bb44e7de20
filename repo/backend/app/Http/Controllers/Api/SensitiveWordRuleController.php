<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSensitiveWordRequest;
use App\Http\Responses\ApiEnvelope;
use App\Models\SensitiveWordRule;
use App\Support\AuditLogger;
use CampusLearn\Moderation\SensitiveWordFilter;
use CampusLearn\Moderation\SensitiveWordRule as DomainRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class SensitiveWordRuleController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly SensitiveWordFilter $filter,
    ) {
    }

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', SensitiveWordRule::class);

        $rules = SensitiveWordRule::orderBy('pattern')->get();
        return ApiEnvelope::data($rules);
    }

    public function store(CreateSensitiveWordRequest $request): JsonResponse
    {
        $this->authorize('create', SensitiveWordRule::class);

        $validated = array_merge($request->validated(), ['created_by' => $request->user()->id]);

        $rule = DB::transaction(function () use ($request, $validated): SensitiveWordRule {
            $rule = SensitiveWordRule::create($validated);
            $this->audit->record($request->user()->id, 'sensitive_word.created', 'sensitive_word_rule', $rule->id, [
                'pattern' => $rule->pattern,
            ]);
            return $rule;
        });

        return ApiEnvelope::data($rule, 201);
    }

    public function check(Request $request): JsonResponse
    {
        $body  = (string) $request->input('body', '');
        $rules = SensitiveWordRule::where('is_active', true)->get();
        $domainRules = $rules->map(fn (SensitiveWordRule $r) => new DomainRule(
            pattern:   $r->pattern,
            matchType: $r->match_type?->value ?? 'substring',
            isActive:  (bool) $r->is_active,
        ))->all();
        $result = $this->filter->inspect($body, $domainRules);

        return ApiEnvelope::data([
            'blocked'       => $result->isBlocked(),
            'blocked_terms' => $result->matches,
        ]);
    }

    public function destroy(Request $request, SensitiveWordRule $sensitiveWordRule): JsonResponse
    {
        $this->authorize('delete', $sensitiveWordRule);

        DB::transaction(function () use ($request, $sensitiveWordRule): void {
            $this->audit->record($request->user()->id, 'sensitive_word.deleted', 'sensitive_word_rule', $sensitiveWordRule->id, [
                'word' => $sensitiveWordRule->word,
            ]);
            $sensitiveWordRule->delete();
        });

        return ApiEnvelope::data(null, 204);
    }
}
