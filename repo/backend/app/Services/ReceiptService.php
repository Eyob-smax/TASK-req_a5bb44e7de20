<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Receipt;
use Illuminate\Support\Facades\DB;

final class ReceiptService
{
    public function generate(Order $order): Receipt
    {
        return DB::transaction(function () use ($order): Receipt {
            $existing = Receipt::where('order_id', $order->id)->lockForUpdate()->first();
            if ($existing !== null) {
                return $existing;
            }

            $number = $this->nextReceiptNumber();

            return Receipt::create([
                'order_id'         => $order->id,
                'receipt_number'   => $number,
                'issued_at'        => now(),
                'rendered_pdf_path' => null,
            ]);
        });
    }

    public function render(Receipt $receipt): string
    {
        $order = $receipt->order()->with('lines.catalogItem', 'user')->first();

        $lines = $receipt->order->lines->map(function ($line) {
            return sprintf(
                '  %-40s x%d  %s',
                $line->catalogItem?->name ?? 'Item',
                $line->quantity,
                '$' . number_format($line->line_total_cents / 100, 2),
            );
        })->implode("\n");

        return sprintf(
            "RECEIPT %s\nIssued: %s\nOrder #%d\nCustomer: %s\n\n%s\n\nSubtotal: $%s\nTax: $%s\nTotal: $%s\n",
            $receipt->receipt_number,
            $receipt->issued_at->format('Y-m-d H:i:s'),
            $receipt->order_id,
            $receipt->order->user->name ?? 'N/A',
            $lines,
            number_format($receipt->order->subtotal_cents / 100, 2),
            number_format($receipt->order->tax_cents / 100, 2),
            number_format($receipt->order->total_cents / 100, 2),
        );
    }

    private function nextReceiptNumber(): string
    {
        $prefix = config('campuslearn.receipts.number_prefix', 'RC');

        $last = Receipt::lockForUpdate()->orderByDesc('id')->value('receipt_number');

        $seq = 1;
        if ($last !== null && preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix . str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }
}
