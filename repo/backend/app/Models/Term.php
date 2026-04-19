<?php

namespace App\Models;

use App\Enums\TermStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Term extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'starts_on', 'ends_on', 'status'];

    protected $casts = [
        'starts_on' => 'date',
        'ends_on' => 'date',
        'status' => TermStatus::class,
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function rosterImports(): HasMany
    {
        return $this->hasMany(RosterImport::class);
    }
}
