<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeCategory extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'label', 'is_taxable'];

    protected $casts = [
        'is_taxable' => 'boolean',
    ];

    public function taxRules(): HasMany
    {
        return $this->hasMany(TaxRule::class);
    }

    public function catalogItems(): HasMany
    {
        return $this->hasMany(CatalogItem::class);
    }
}
