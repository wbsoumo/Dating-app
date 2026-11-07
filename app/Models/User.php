<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'phone_verified_at',
        'email',
        'email_verified_at',
        'password',
        'gender',
        'dob',
        'age',
        'status',
        'is_verified',
        'verification_submitted_at',
        'verification_photo',
        'is_premium',
        'premium_expires_at',
        'custom_swipe_limit',
        'is_demo',
        'fcm_token',
        'last_active_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'dob' => 'date',
            'age' => 'integer',
            'is_verified' => 'boolean',
            'verification_submitted_at' => 'datetime',
            'is_premium' => 'boolean',
            'premium_expires_at' => 'datetime',
            'custom_swipe_limit' => 'integer',
            'is_demo' => 'boolean',
            'last_active_at' => 'datetime',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function location(): HasOne
    {
        return $this->hasOne(UserLocation::class);
    }

    public function preferences(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class)->orderBy('is_primary', 'desc')->orderBy('sort_order', 'asc');
    }

    public function primaryPhoto(): HasOne
    {
        return $this->hasOne(Photo::class)->where('is_primary', true);
    }

    public function interests(): BelongsToMany
    {
        return $this->belongsToMany(Interest::class, 'interest_user');
    }

    public function swipes(): HasMany
    {
        return $this->hasMany(Swipe::class, 'user_id');
    }

    public function receivedSwipes(): HasMany
    {
        return $this->hasMany(Swipe::class, 'target_user_id');
    }

    public function matchesAsUser1(): HasMany
    {
        return $this->hasMany(UserMatch::class, 'user1_id');
    }

    public function matchesAsUser2(): HasMany
    {
        return $this->hasMany(UserMatch::class, 'user2_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(UserSubscription::class)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest('id');
    }

    public function starWallet(): HasOne
    {
        return $this->hasOne(StarWallet::class);
    }

    public function starTransactions(): HasMany
    {
        return $this->hasMany(StarTransaction::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function reportsMade(): HasMany
    {
        return $this->hasMany(UserReport::class, 'reporter_id');
    }

    public function reportsReceived(): HasMany
    {
        return $this->hasMany(UserReport::class, 'reported_user_id');
    }

    public function blockedUsers(): HasMany
    {
        return $this->hasMany(UserBlock::class, 'user_id');
    }

    public function blockedByUsers(): HasMany
    {
        return $this->hasMany(UserBlock::class, 'blocked_user_id');
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function hasActivePremium(): bool
    {
        if ($this->is_premium && ($this->premium_expires_at === null || $this->premium_expires_at->isFuture())) {
            return true;
        }

        $activeSub = $this->activeSubscription;
        return $activeSub !== null && $activeSub->expires_at->isFuture();
    }

    public function getDailySwipeLimit(): int
    {
        // 1. User-specific override
        if ($this->custom_swipe_limit !== null) {
            return $this->custom_swipe_limit; // -1 = unlimited
        }

        // 2. Active subscription limit
        if ($this->hasActivePremium()) {
            $sub = $this->activeSubscription;
            if ($sub && $sub->plan) {
                return $sub->plan->daily_swipe_limit; // -1 = unlimited
            }
            return -1; // Default premium is unlimited
        }

        // 3. System default free limit
        return (int) Setting::get('free_daily_swipes', 50);
    }

    public function getSwipesUsedToday(): int
    {
        return $this->swipes()
            ->whereDate('created_at', Carbon::today())
            ->count();
    }

    public function getSwipesRemainingToday(): int
    {
        $limit = $this->getDailySwipeLimit();
        if ($limit === -1) {
            return 999999; // Unlimited
        }

        $used = $this->getSwipesUsedToday();
        return max(0, $limit - $used);
    }

    public function canSwipeToday(): bool
    {
        $limit = $this->getDailySwipeLimit();
        if ($limit === -1) {
            return true;
        }

        return $this->getSwipesRemainingToday() > 0;
    }

    public function getOrCreateWallet(): StarWallet
    {
        return $this->starWallet ?: StarWallet::create([
            'user_id' => $this->id,
            'balance' => (int) Setting::get('free_daily_stars', 5),
            'last_daily_bonus_at' => Carbon::today(),
        ]);
    }
}
