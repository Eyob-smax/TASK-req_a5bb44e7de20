<?php

namespace App\Models;

use App\Enums\NotificationCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'category',
        'title_template',
        'body_template',
    ];

    protected $casts = [
        'category' => NotificationCategory::class,
    ];
}
