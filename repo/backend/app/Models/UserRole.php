<?php

namespace App\Models;

use App\Enums\RoleName;
use Illuminate\Database\Eloquent\Model;

/**
 * Convenience shim for tests: accepts `role` (RoleName enum or string) and
 * translates it to a `role_id` FK via the `roles` table, then inserts into
 * `role_assignments`. Scope type defaults to 'global' when null.
 */
class UserRole extends Model
{
    protected $table = 'role_assignments';

    protected $fillable = [
        'user_id',
        'role_id',
        'scope_type',
        'scope_id',
        'granted_by',
        'granted_at',
    ];

    public static function create(array $attributes = []): static
    {
        $roleValue = $attributes['role'] ?? null;
        if ($roleValue instanceof RoleName) {
            $roleValue = $roleValue->value;
        }

        $role = Role::firstOrCreate(
            ['name' => $roleValue],
            ['label' => ucfirst((string) $roleValue)],
        );

        return parent::create([
            'user_id'    => $attributes['user_id'],
            'role_id'    => $role->id,
            'scope_type' => $attributes['scope_type'] ?? 'global',
            'scope_id'   => $attributes['scope_id'] ?? null,
            'granted_by' => null,
            'granted_at' => now(),
        ]);
    }
}
