<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiEnvelope;
use App\Models\DiagnosticExport;
use App\Services\DiagnosticExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DiagnosticExportController extends Controller
{
    public function __construct(
        private readonly DiagnosticExportService $exportService,
    ) {
    }

    public function trigger(Request $request): JsonResponse
    {
        $this->authorize('create', DiagnosticExport::class);

        $export = $this->exportService->trigger($request->user());
        return ApiEnvelope::data($export->load('initiator'), 201);
    }

    public function index(): JsonResponse
    {
        $this->authorize('create', DiagnosticExport::class);

        $exports = $this->exportService->list();
        return ApiEnvelope::data($exports);
    }
}
