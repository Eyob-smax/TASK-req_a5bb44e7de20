<?php

namespace App\Models;

use App\Enums\SensitiveWordMatchType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensitiveWordRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'pattern',
        'match_type',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'match_type' => SensitiveWordMatchType::class,
        'is_active' => 'boolean',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
