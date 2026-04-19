<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'catalog_item_id',
        'description',
        'quantity',
        'unit_price_cents',
        'tax_rule_snapshot',
        'line_total_cents',
    ];

    protected $casts = [
        'tax_rule_snapshot' => 'array',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(CatalogItem::class);
    }
}
