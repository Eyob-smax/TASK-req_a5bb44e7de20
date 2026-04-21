<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiEnvelope;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewDashboard');

        $summary = $this->dashboardService->summaryFor($request->user());
        return ApiEnvelope::data($summary);
    }
}
