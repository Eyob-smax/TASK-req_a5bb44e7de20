<?php

namespace App\Models;

use App\Enums\ContentState;
use App\Enums\ThreadType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Thread extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'section_id',
        'author_id',
        'thread_type',
        'qa_enabled',
        'title',
        'body',
        'state',
        'edited_at',
    ];

    protected $casts = [
        'thread_type' => ThreadType::class,
        'state' => ContentState::class,
        'qa_enabled' => 'boolean',
        'edited_at' => 'datetime',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
