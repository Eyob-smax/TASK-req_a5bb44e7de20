<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiEnvelope;
use App\Models\BackupJob;
use App\Services\BackupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BackupController extends Controller
{
    public function __construct(
        private readonly BackupService $backupService,
    ) {
    }

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', BackupJob::class);

        $backups = $this->backupService->list();
        return ApiEnvelope::data($backups);
    }

    public function trigger(Request $request): JsonResponse
    {
        $this->authorize('create', BackupJob::class);

        $job = $this->backupService->trigger($request->user());
        return ApiEnvelope::data($job, 202);
    }

    public function show(int $id): JsonResponse
    {
        $this->authorize('view', BackupJob::class);

        $backup = $this->backupService->find($id);
        return ApiEnvelope::data($backup);
    }
}
