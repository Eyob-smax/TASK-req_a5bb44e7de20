<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAdminSettingsRequest;
use App\Http\Responses\ApiEnvelope;
use App\Services\AdminSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminSettingsController extends Controller
{
    public function __construct(
        private readonly AdminSettingsService $settingsService,
    ) {
    }

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\SystemSetting::class);

        return ApiEnvelope::data($this->settingsService->all());
    }

    public function update(UpdateAdminSettingsRequest $request): JsonResponse
    {
        $this->authorize('update', \App\Models\SystemSetting::class);

        $settings = $this->settingsService->update($request->user(), $request->validated('settings'));
        return ApiEnvelope::data($settings);
    }

    public function auditLog(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\SystemSetting::class);

        $entries = $this->settingsService->auditLog($request->only(['action', 'actor_id', 'target_type', 'from', 'to']));
        return ApiEnvelope::data($entries);
    }
}
