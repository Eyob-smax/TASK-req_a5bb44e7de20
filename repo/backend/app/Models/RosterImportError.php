<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RosterImportError extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'roster_import_id',
        'row_number',
        'error_code',
        'message',
        'raw_row',
    ];

    protected $casts = [
        'raw_row' => 'array',
        'created_at' => 'datetime',
    ];

    public function rosterImport(): BelongsTo
    {
        return $this->belongsTo(RosterImport::class);
    }
}
