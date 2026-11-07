<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;

    protected $fillable = [
        'identifier',
        'code',
        'expires_at',
        'is_verified',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'is_verified' => 'boolean',
        ];
    }

    public static function generate(string $identifier, int $expiryMinutes = 10): self
    {
        // Invalidate past unverified OTPs for this identifier
        self::where('identifier', $identifier)
            ->where('is_verified', false)
            ->delete();

        // Check if demo OTP is configured
        $demoCode = Setting::get('demo_otp_code');
        $code = !empty($demoCode) ? $demoCode : str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        return self::create([
            'identifier' => $identifier,
            'code' => $code,
            'expires_at' => Carbon::now()->addMinutes($expiryMinutes),
            'is_verified' => false,
        ]);
    }

    public static function verify(string $identifier, string $code): bool
    {
        $otp = self::where('identifier', $identifier)
            ->where('code', $code)
            ->where('is_verified', false)
            ->where('expires_at', '>', Carbon::now())
            ->latest('id')
            ->first();

        if ($otp) {
            $otp->update(['is_verified' => true]);
            return true;
        }

        return false;
    }
}
