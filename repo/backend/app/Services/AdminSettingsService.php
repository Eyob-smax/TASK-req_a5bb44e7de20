<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLogEntry;
use App\Models\SystemSetting;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class AdminSettingsService
{
    // Keys that are settable via the admin settings API
    private const ALLOWED_KEYS = [
        'edit_window_minutes',
        'order_auto_close_minutes',
        'penalty_grace_days',
        'penalty_rate_bps',
        'fanout_batch_size',
        'backup_retention_days',
        'receipt_number_prefix',
    ];

    public function __construct(
        private readonly AuditLogger $audit,
    ) {
    }

    public function all(): array
    {
        return SystemSetting::whereIn('key', self::ALLOWED_KEYS)
            ->get()
            ->keyBy('key')
            ->map(fn (SystemSetting $s) => $s->value)
            ->toArray();
    }

    public function update(User $actor, array $settings): array
    {
        return DB::transaction(function () use ($actor, $settings): array {
            foreach ($settings as $key => $value) {
                if (! in_array($key, self::ALLOWED_KEYS, true)) {
                    continue;
                }

                SystemSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'updated_by' => $actor->id],
                );
            }

            $this->audit->record($actor->id, 'admin_settings.updated', 'system_settings', null, [
                'keys' => array_keys($settings),
            ]);

            return $this->all();
        });
    }

    public function auditLog(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        $query = AuditLogEntry::with('actor')
            ->orderByDesc('id');

        if (! empty($filters['action'])) {
            $query->where('action', 'like', '%' . $filters['action'] . '%');
        }

        if (! empty($filters['actor_id'])) {
            $query->where('actor_user_id', (int) $filters['actor_id']);
        }

        if (! empty($filters['target_type'])) {
            $query->where('target_type', $filters['target_type']);
        }

        if (! empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        return $query->paginate($perPage);
    }
}
