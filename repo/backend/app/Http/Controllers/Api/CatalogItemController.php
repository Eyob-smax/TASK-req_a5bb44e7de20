<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCatalogItemRequest;
use App\Http\Requests\UpdateCatalogItemRequest;
use App\Http\Responses\ApiEnvelope;
use App\Models\CatalogItem;
use App\Services\CatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CatalogItemController extends Controller
{
    public function __construct(
        private readonly CatalogService $catalogService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $activeOnly = ! $request->boolean('all');
        $items      = $this->catalogService->list($activeOnly);
        return ApiEnvelope::data($items);
    }

    public function store(CreateCatalogItemRequest $request): JsonResponse
    {
        $this->authorize('create', CatalogItem::class);

        $item = $this->catalogService->create($request->user(), $request->validated());
        return ApiEnvelope::data($item, 201);
    }

    public function update(UpdateCatalogItemRequest $request, CatalogItem $catalogItem): JsonResponse
    {
        $this->authorize('update', $catalogItem);

        $item = $this->catalogService->update($request->user(), $catalogItem, $request->validated());
        return ApiEnvelope::data($item);
    }
}
