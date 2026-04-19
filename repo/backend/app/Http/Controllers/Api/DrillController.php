<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateDrillRecordRequest;
use App\Http\Responses\ApiEnvelope;
use App\Models\DrDrillRecord;
use App\Services\DrillRecordService;
use Illuminate\Http\JsonResponse;

final class DrillController extends Controller
{
    public function __construct(
        private readonly DrillRecordService $drillService,
    ) {
    }

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', DrDrillRecord::class);

        $records = $this->drillService->list();
        return ApiEnvelope::data($records);
    }

    public function store(CreateDrillRecordRequest $request): JsonResponse
    {
        $this->authorize('create', DrDrillRecord::class);

        $record = $this->drillService->record($request->user(), $request->validated());
        return ApiEnvelope::data($record, 201);
    }
}
