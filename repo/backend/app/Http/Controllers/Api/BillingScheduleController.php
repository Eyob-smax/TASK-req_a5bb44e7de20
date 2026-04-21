<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBillingScheduleRequest;
use App\Http\Responses\ApiEnvelope;
use App\Models\BillSchedule;
use App\Services\BillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BillingScheduleController extends Controller
{
    public function __construct(
        private readonly BillingService $billingService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', BillSchedule::class);

        $schedules = $this->billingService->listSchedules($request->user());
        return ApiEnvelope::data($schedules);
    }

    public function update(UpdateBillingScheduleRequest $request, BillSchedule $billSchedule): JsonResponse
    {
        $this->authorize('update', $billSchedule);

        $schedule = $this->billingService->updateSchedule(
            $request->user(),
            $billSchedule,
            $request->validated(),
        );
        return ApiEnvelope::data($schedule);
    }
}
