<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateFeeCategoryRequest;
use App\Http\Requests\CreateTaxRuleRequest;
use App\Http\Requests\UpdateFeeCategoryRequest;
use App\Http\Requests\UpdateTaxRuleRequest;
use App\Http\Responses\ApiEnvelope;
use App\Models\FeeCategory;
use App\Models\TaxRule;
use App\Services\FeeCategoryService;
use App\Services\TaxRuleService;
use Illuminate\Http\JsonResponse;

final class FeeCategoryController extends Controller
{
    public function __construct(
        private readonly FeeCategoryService $feeCategoryService,
        private readonly TaxRuleService $taxRuleService,
    ) {
    }

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', FeeCategory::class);

        $categories = $this->feeCategoryService->list();
        return ApiEnvelope::data($categories);
    }

    public function store(CreateFeeCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', FeeCategory::class);

        $category = $this->feeCategoryService->create($request->user(), $request->validated());
        return ApiEnvelope::data($category, 201);
    }

    public function update(UpdateFeeCategoryRequest $request, FeeCategory $feeCategory): JsonResponse
    {
        $this->authorize('update', $feeCategory);

        $category = $this->feeCategoryService->update($request->user(), $feeCategory, $request->validated());
        return ApiEnvelope::data($category);
    }

    public function storeTaxRule(CreateTaxRuleRequest $request, FeeCategory $feeCategory): JsonResponse
    {
        $this->authorize('update', $feeCategory);

        $rule = $this->taxRuleService->create($request->user(), $feeCategory, $request->validated());
        return ApiEnvelope::data($rule, 201);
    }

    public function updateTaxRule(UpdateTaxRuleRequest $request, FeeCategory $feeCategory, TaxRule $taxRule): JsonResponse
    {
        $this->authorize('update', $feeCategory);

        $rule = $this->taxRuleService->update($request->user(), $taxRule, $request->validated());
        return ApiEnvelope::data($rule);
    }
}
