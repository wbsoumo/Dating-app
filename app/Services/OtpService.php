<?php

namespace App\Services;

use App\Models\Otp;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    /**
     * Send OTP to phone or email identifier.
     */
    public function sendOtp(string $identifier, string $type = 'verification'): array
    {
        $otp = Otp::generate($identifier);
        $provider = Setting::get('otp_provider', 'demo');

        if ($provider === 'smtp' && filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            try {
                // Send email
                Mail::raw("Your {$type} code is: {$otp->code}. It expires in 10 minutes.", function ($message) use ($identifier) {
                    $message->to($identifier)
                        ->subject('Your DilSe Match Verification Code');
                });
            } catch (\Throwable $e) {
                Log::error("Failed to send OTP email: " . $e->getMessage());
            }
        }

        return [
            'identifier' => $identifier,
            'expires_at' => $otp->expires_at->toIso8601String(),
            // Expose demo code only if running in demo mode
            'demo_code' => ($provider === 'demo') ? $otp->code : null,
            'message' => 'OTP sent successfully.',
        ];
    }

    /**
     * Verify OTP code.
     */
    public function verifyOtp(string $identifier, string $code): bool
    {
        return Otp::verify($identifier, $code);
    }
}
