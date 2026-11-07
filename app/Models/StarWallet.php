<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StarWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'last_daily_bonus_at',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'integer',
            'last_daily_bonus_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(StarTransaction::class, 'wallet_id')->latest('id');
    }

    public function credit(int $amount, string $description, ?string $refType = null, ?string $refId = null): StarTransaction
    {
        $this->balance += $amount;
        $this->save();

        return $this->transactions()->create([
            'user_id' => $this->user_id,
            'type' => 'credit',
            'amount' => $amount,
            'balance_after' => $this->balance,
            'description' => $description,
            'reference_type' => $refType,
            'reference_id' => $refId,
        ]);
    }

    public function debit(int $amount, string $description, ?string $refType = null, ?string $refId = null): ?StarTransaction
    {
        if ($this->balance < $amount) {
            return null;
        }

        $this->balance -= $amount;
        $this->save();

        return $this->transactions()->create([
            'user_id' => $this->user_id,
            'type' => 'debit',
            'amount' => $amount,
            'balance_after' => $this->balance,
            'description' => $description,
            'reference_type' => $refType,
            'reference_id' => $refId,
        ]);
    }
}
