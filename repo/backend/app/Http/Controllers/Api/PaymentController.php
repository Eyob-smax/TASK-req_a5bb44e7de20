<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompletePaymentRequest;
use App\Http\Requests\InitiatePaymentRequest;
use App\Http\Responses\ApiEnvelope;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;

final class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {
    }

    public function initiate(InitiatePaymentRequest $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $attempt = $this->paymentService->initiate(
            $order,
            $request->string('method')->toString(),
            $request->user(),
            $request->string('kiosk_id')->toString() ?: null,
        );

        return ApiEnvelope::data($attempt, 201);
    }

    public function complete(CompletePaymentRequest $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $attempt = PaymentAttempt::findOrFail($request->integer('attempt_id'));

        if ($attempt->order_id !== $order->id) {
            abort(422, 'Payment attempt does not belong to this order.');
        }

        $order   = $this->paymentService->complete($order, $attempt, $request->user());

        return ApiEnvelope::data($order);
    }
}
