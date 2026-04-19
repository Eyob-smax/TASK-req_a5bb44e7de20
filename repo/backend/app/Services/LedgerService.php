<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\LedgerEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class LedgerService
{
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = LedgerEntry::with(['user', 'bill', 'order'])
            ->orderByDesc('created_at');

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (isset($filters['bill_id'])) {
            $query->where('bill_id', $filters['bill_id']);
        }
        if (isset($filters['order_id'])) {
            $query->where('order_id', $filters['order_id']);
        }
        if (isset($filters['entry_type'])) {
            $query->where('entry_type', $filters['entry_type']);
        }

        return $query->paginate($perPage);
    }
}
