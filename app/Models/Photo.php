<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Photo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'url',
        'path',
        'thumbnail_url',
        'is_primary',
        'sort_order',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_approved' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
