<?php

namespace App\Models;

use App\Enums\GradeItemState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'title',
        'max_score',
        'weight_bps',
        'state',
        'published_at',
    ];

    protected $casts = [
        'state' => GradeItemState::class,
        'published_at' => 'datetime',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(GradeItemScore::class);
    }
}
