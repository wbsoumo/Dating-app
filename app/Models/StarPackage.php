<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StarPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'stars_count',
        'price',
        'currency',
        'discount_percent',
        'badge',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'stars_count' => 'integer',
            'price' => 'float',
            'discount_percent' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
