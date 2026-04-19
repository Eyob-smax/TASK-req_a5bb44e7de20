<?php

namespace App\Models;

use App\Enums\RoleName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['name', 'label'];

    protected $casts = [
        'name' => RoleName::class,
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(RoleAssignment::class);
    }
}
