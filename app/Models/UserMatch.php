<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMatch extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'user1_id',
        'user2_id',
        'status', // active, unmatched
        'matched_at',
        'unmatched_by',
        'unmatch_reason',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'matched_at' => 'datetime',
            'last_message_at' => 'datetime',
        ];
    }

    public function user1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    public function user2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user2_id');
    }

    public function unmatchedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unmatched_by');
    }

    /**
     * Get the other user in the match given one user's ID.
     */
    public function getPartner(int $userId): ?User
    {
        if ($this->user1_id === $userId) {
            return $this->user2;
        }

        if ($this->user2_id === $userId) {
            return $this->user1;
        }

        return null;
    }
}
