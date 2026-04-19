<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Responses\ApiEnvelope;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->list($request->user());
        return ApiEnvelope::data($orders);
    }

    public function store(CreateOrderRequest $request): JsonResponse
    {
        $this->authorize('create', Order::class);

        $order = $this->orderService->create($request->user(), $request->input('lines'));
        return ApiEnvelope::data($order, 201);
    }

    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        return ApiEnvelope::data($order->load(['lines.catalogItem', 'receipt']));
    }

    public function destroy(Request $request, Order $order): JsonResponse
    {
        $this->authorize('delete', $order);

        $this->orderService->cancel($request->user(), $order);
        return ApiEnvelope::data(null, 204);
    }

    public function timeline(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $events = $this->orderService->timeline($order);
        return ApiEnvelope::data($events);
    }
}
