<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\AuditLogEntry;
use Illuminate\Support\Str;

final class AuditLogger
{
    /**
     * Record a privileged-mutation audit entry. Intended to be called inside
     * the same DB transaction as the mutation, so both commit or both roll back.
     *
     * @param array<string, mixed> $payload
     */
    public function record(
        ?int $actorUserId,
        string $action,
        string $targetType,
        ?int $targetId,
        array $payload = [],
    ): AuditLogEntry {
        return AuditLogEntry::create([
            'actor_user_id'  => $actorUserId,
            'action'         => $action,
            'target_type'    => $targetType,
            'target_id'      => $targetId,
            'payload'        => $payload,
            'correlation_id' => $this->correlationId(),
            'created_at'     => now(),
        ]);
    }

    private function correlationId(): string
    {
        $attr = request()->attributes->get('correlation_id');
        if (is_string($attr) && $attr !== '') {
            return $attr;
        }
        return (string) Str::uuid();
    }
}
