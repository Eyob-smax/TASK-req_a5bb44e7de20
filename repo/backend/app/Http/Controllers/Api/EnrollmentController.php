<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiEnvelope;
use App\Models\Enrollment;
use App\Services\EnrollmentDecisionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EnrollmentController extends Controller
{
    public function __construct(
        private readonly EnrollmentDecisionService $decisionService,
    ) {
    }

    public function approve(Request $request, Enrollment $enrollment): JsonResponse
    {
        $this->authorize('update', $enrollment);

        $enrollment = $this->decisionService->approve($request->user(), $enrollment);
        return ApiEnvelope::data($enrollment);
    }

    public function deny(Request $request, Enrollment $enrollment): JsonResponse
    {
        $this->authorize('update', $enrollment);

        $enrollment = $this->decisionService->deny($request->user(), $enrollment);
        return ApiEnvelope::data($enrollment);
    }
}
