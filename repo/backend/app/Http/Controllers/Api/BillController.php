<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateBillRequest;
use App\Http\Responses\ApiEnvelope;
use App\Models\Bill;
use App\Models\BillSchedule;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BillController extends Controller
{
    public function __construct(
        private readonly BillingService $billingService,
    ) {
    }

    public function mineIndex(Request $request): JsonResponse
    {
        $bills = $this->billingService->userBills($request->user());
        return ApiEnvelope::data($bills);
    }

    public function show(Bill $bill): JsonResponse
    {
        $this->authorize('view', $bill);

        return ApiEnvelope::data($bill->load('lines'));
    }

    public function adminIndex(): JsonResponse
    {
        $this->authorize('adminIndex', Bill::class);

        $bills = $this->billingService->adminBills();
        return ApiEnvelope::data($bills);
    }

    public function adminGenerate(GenerateBillRequest $request): JsonResponse
    {
        $this->authorize('adminGenerate', Bill::class);

        $user = User::findOrFail($request->integer('user_id'));
        $type = $request->string('type')->toString();

        if ($type === 'initial') {
            $schedule = BillSchedule::findOrFail($request->integer('bill_schedule_id'));
            $bill = $this->billingService->generateInitialBill($user, $schedule);
        } else {
            $bill = $this->billingService->generateSupplemental($user, [
                'amount_cents'    => $request->integer('amount_cents'),
                'catalog_item_id' => null,
            ], $request->string('reason')->toString());
        }

        return ApiEnvelope::data($bill, 201);
    }
}
