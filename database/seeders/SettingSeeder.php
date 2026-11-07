<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'app_name',
                'value' => 'DilSe Match',
                'group' => 'general',
                'type' => 'text',
                'label' => 'Application Name',
                'description' => 'The brand name of the dating platform.',
            ],
            [
                'key' => 'app_logo',
                'value' => '/assets/adminlte/dist/img/logo.png',
                'group' => 'general',
                'type' => 'text',
                'label' => 'Application Logo URL',
                'description' => 'URL or path to application logo.',
            ],
            [
                'key' => 'currency',
                'value' => 'INR',
                'group' => 'general',
                'type' => 'text',
                'label' => 'Default Currency Code',
                'description' => 'ISO 4217 Currency Code (e.g. INR, USD).',
            ],
            [
                'key' => 'currency_symbol',
                'value' => '₹',
                'group' => 'general',
                'type' => 'text',
                'label' => 'Currency Symbol',
                'description' => 'Display symbol for prices.',
            ],
            [
                'key' => 'support_email',
                'value' => 'support@dilsematch.com',
                'group' => 'general',
                'type' => 'text',
                'label' => 'Customer Support Email',
                'description' => 'Inquiries and feedback contact email.',
            ],
            [
                'key' => 'default_timezone',
                'value' => 'Asia/Kolkata',
                'group' => 'general',
                'type' => 'text',
                'label' => 'Default Timezone',
                'description' => 'Standard timezone for application dates.',
            ],

            // API Settings
            [
                'key' => 'api_rate_limit',
                'value' => '60',
                'group' => 'api',
                'type' => 'number',
                'label' => 'API Rate Limit (Requests/Min)',
                'description' => 'Max requests per minute per IP or authenticated user.',
            ],
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'group' => 'api',
                'type' => 'boolean',
                'label' => 'API Maintenance Mode',
                'description' => 'Disable API access for maintenance updates.',
            ],
            [
                'key' => 'min_app_version',
                'value' => '1.0.0',
                'group' => 'api',
                'type' => 'text',
                'label' => 'Minimum Required Mobile App Version',
                'description' => 'Force update threshold for mobile clients.',
            ],
            [
                'key' => 'latest_app_version',
                'value' => '1.0.0',
                'group' => 'api',
                'type' => 'text',
                'label' => 'Latest Mobile App Version',
                'description' => 'Recommended version for mobile clients.',
            ],

            // Razorpay Credentials & Mode
            [
                'key' => 'razorpay_mode',
                'value' => 'test',
                'group' => 'razorpay',
                'type' => 'select',
                'label' => 'Razorpay Gateway Mode',
                'description' => 'Switch between test and live sandbox modes.',
            ],
            [
                'key' => 'razorpay_key_id',
                'value' => 'rzp_test_YourKeyIdHere',
                'group' => 'razorpay',
                'type' => 'text',
                'label' => 'Razorpay Key ID',
                'description' => 'Public API Key for Razorpay Checkout.',
            ],
            [
                'key' => 'razorpay_key_secret',
                'value' => 'YourSecretKeyHere',
                'group' => 'razorpay',
                'type' => 'password',
                'label' => 'Razorpay Key Secret',
                'description' => 'Private server secret for HMAC-SHA256 signature verification.',
            ],
            [
                'key' => 'razorpay_webhook_secret',
                'value' => 'YourWebhookSecretHere',
                'group' => 'razorpay',
                'type' => 'password',
                'label' => 'Razorpay Webhook Secret',
                'description' => 'Secret used to verify Razorpay webhook signatures.',
            ],

            // OTP & SMTP Settings
            [
                'key' => 'otp_provider',
                'value' => 'demo',
                'group' => 'otp_smtp',
                'type' => 'select',
                'label' => 'OTP Verification Provider',
                'description' => 'Select "demo" to use fixed OTP code, or "smtp" for email delivery.',
            ],
            [
                'key' => 'demo_otp_code',
                'value' => '123456',
                'group' => 'otp_smtp',
                'type' => 'text',
                'label' => 'Demo Fixed OTP Code',
                'description' => 'Default OTP used when provider is in demo mode.',
            ],
            [
                'key' => 'smtp_host',
                'value' => 'smtp.mailtrap.io',
                'group' => 'otp_smtp',
                'type' => 'text',
                'label' => 'SMTP Server Host',
                'description' => 'Outgoing mail server host.',
            ],
            [
                'key' => 'smtp_port',
                'value' => '587',
                'group' => 'otp_smtp',
                'type' => 'number',
                'label' => 'SMTP Server Port',
                'description' => 'Typically 587 (TLS) or 465 (SSL).',
            ],
            [
                'key' => 'smtp_username',
                'value' => '',
                'group' => 'otp_smtp',
                'type' => 'text',
                'label' => 'SMTP Username',
                'description' => 'Email account username.',
            ],
            [
                'key' => 'smtp_password',
                'value' => '',
                'group' => 'otp_smtp',
                'type' => 'password',
                'label' => 'SMTP Password',
                'description' => 'Email account password or app password.',
            ],
            [
                'key' => 'smtp_encryption',
                'value' => 'tls',
                'group' => 'otp_smtp',
                'type' => 'text',
                'label' => 'SMTP Encryption',
                'description' => 'Encryption protocol: tls or ssl.',
            ],
            [
                'key' => 'smtp_from_address',
                'value' => 'noreply@dilsematch.com',
                'group' => 'otp_smtp',
                'type' => 'text',
                'label' => 'From Email Address',
                'description' => 'Sender address displayed on transactional emails.',
            ],
            [
                'key' => 'smtp_from_name',
                'value' => 'DilSe Match Support',
                'group' => 'otp_smtp',
                'type' => 'text',
                'label' => 'From Sender Name',
                'description' => 'Display name for sent emails.',
            ],

            // Location & Radius Settings
            [
                'key' => 'default_latitude',
                'value' => '28.6139',
                'group' => 'location',
                'type' => 'text',
                'label' => 'Default Center Latitude (New Delhi)',
                'description' => 'Default fallback latitude when user GPS is unavailable.',
            ],
            [
                'key' => 'default_longitude',
                'value' => '77.2090',
                'group' => 'location',
                'type' => 'text',
                'label' => 'Default Center Longitude (New Delhi)',
                'description' => 'Default fallback longitude when user GPS is unavailable.',
            ],
            [
                'key' => 'default_city',
                'value' => 'New Delhi',
                'group' => 'location',
                'type' => 'text',
                'label' => 'Default City Name',
                'description' => 'Default fallback city.',
            ],
            [
                'key' => 'distance_unit',
                'value' => 'km',
                'group' => 'location',
                'type' => 'select',
                'label' => 'Distance Measurement Unit',
                'description' => 'Kilometers (km) or Miles (miles).',
            ],
            [
                'key' => 'default_radius',
                'value' => '50',
                'group' => 'location',
                'type' => 'number',
                'label' => 'Default Search Discovery Radius',
                'description' => 'Initial radius in selected unit.',
            ],
            [
                'key' => 'max_radius',
                'value' => '500',
                'group' => 'location',
                'type' => 'number',
                'label' => 'Maximum Search Radius Allowed',
                'description' => 'Upper limit for distance slider in preferences.',
            ],

            // Matching Algorithm Weights
            [
                'key' => 'weight_proximity',
                'value' => '40',
                'group' => 'matching',
                'type' => 'number',
                'label' => 'Proximity Weight (%)',
                'description' => 'Influence of geographic distance on match score.',
            ],
            [
                'key' => 'weight_interests',
                'value' => '30',
                'group' => 'matching',
                'type' => 'number',
                'label' => 'Interests Overlap Weight (%)',
                'description' => 'Influence of shared hobbies and interests.',
            ],
            [
                'key' => 'weight_goals',
                'value' => '20',
                'group' => 'matching',
                'type' => 'number',
                'label' => 'Relationship Goals Weight (%)',
                'description' => 'Influence of matching relationship objectives.',
            ],
            [
                'key' => 'weight_religion',
                'value' => '10',
                'group' => 'matching',
                'type' => 'number',
                'label' => 'Religious Preference Weight (%)',
                'description' => 'Influence of religious compatibility.',
            ],

            // Swipe Limits & Allowances
            [
                'key' => 'free_daily_swipes',
                'value' => '50',
                'group' => 'swipes',
                'type' => 'number',
                'label' => 'Free Daily Swipes Limit',
                'description' => 'Number of swipes per 24 hours for free users.',
            ],
            [
                'key' => 'free_daily_stars',
                'value' => '1',
                'group' => 'swipes',
                'type' => 'number',
                'label' => 'Free Daily Stars (Super Likes)',
                'description' => 'Complimentary stars given per day to free users.',
            ],
            [
                'key' => 'super_like_star_cost',
                'value' => '1',
                'group' => 'swipes',
                'type' => 'number',
                'label' => 'Star Cost per Super Like',
                'description' => 'Number of stars deducted when sending a Super Like.',
            ],

            // User Verification & Photos
            [
                'key' => 'auto_verify_users',
                'value' => '0',
                'group' => 'verification',
                'type' => 'boolean',
                'label' => 'Auto-Approve Verification Requests',
                'description' => 'If disabled, requires manual admin review.',
            ],
            [
                'key' => 'max_photos_per_user',
                'value' => '6',
                'group' => 'verification',
                'type' => 'number',
                'label' => 'Max Photos Per User Profile',
                'description' => 'Maximum upload limit per account.',
            ],
            [
                'key' => 'allow_account_deletion',
                'value' => '1',
                'group' => 'security',
                'type' => 'boolean',
                'label' => 'Allow User Account Deletion',
                'description' => 'Permit users to delete their account from settings.',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
