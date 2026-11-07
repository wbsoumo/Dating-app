<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'payment_id',
        'signature',
        'amount',
        'currency',
        'item_type', // subscription, star_package
        'item_id',
        'status', // created, authorized, captured, failed, refunded
        'method',
        'razorpay_response',
        'error_code',
        'error_description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'razorpay_response' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(PaymentRefund::class);
    }

    public function getItem()
    {
        if ($this->item_type === 'subscription') {
            return SubscriptionPlan::find($this->item_id);
        }

        if ($this->item_type === 'star_package') {
            return StarPackage::find($this->item_id);
        }

        return null;
    }
}
