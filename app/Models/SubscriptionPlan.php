<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'duration_days',
        'daily_swipe_limit',
        'daily_star_allowance',
        'can_see_likes',
        'can_rewind',
        'can_boost',
        'badge_color',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'duration_days' => 'integer',
            'daily_swipe_limit' => 'integer',
            'daily_star_allowance' => 'integer',
            'can_see_likes' => 'boolean',
            'can_rewind' => 'boolean',
            'can_boost' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class, 'plan_id');
    }
}
