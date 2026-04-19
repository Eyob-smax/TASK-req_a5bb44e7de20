<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiEnvelope;
use App\Models\Order;
use App\Models\Receipt;
use App\Services\ReceiptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class ReceiptController extends Controller
{
    public function __construct(
        private readonly ReceiptService $receiptService,
    ) {
    }

    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $receipt = $order->receipt;
        if ($receipt === null) {
            return ApiEnvelope::error('RECEIPT_NOT_FOUND', 'No receipt for this order.', 404);
        }
        return ApiEnvelope::data($receipt);
    }

    public function print(Order $order): Response
    {
        $this->authorize('view', $order);

        $receipt = $order->receipt;
        if ($receipt === null) {
            abort(404);
        }

        $text = $this->receiptService->render($receipt->load('order.lines.catalogItem', 'order.user'));

        return new Response($text, 200, [
            'Content-Type'        => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'inline; filename="receipt-' . $receipt->receipt_number . '.txt"',
        ]);
    }
}
