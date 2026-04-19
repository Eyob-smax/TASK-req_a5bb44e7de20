<?php

namespace App\Models;

use App\Enums\RosterImportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RosterImport extends Model
{
    protected $fillable = [
        'term_id',
        'initiated_by',
        'source_filename',
        'row_count',
        'success_count',
        'error_count',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'status' => RosterImportStatus::class,
        'completed_at' => 'datetime',
    ];

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function errors(): HasMany
    {
        return $this->hasMany(RosterImportError::class);
    }
}
