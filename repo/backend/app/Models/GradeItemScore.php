<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeItemScore extends Model
{
    protected $fillable = [
        'grade_item_id',
        'user_id',
        'score',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function gradeItem(): BelongsTo
    {
        return $this->belongsTo(GradeItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
