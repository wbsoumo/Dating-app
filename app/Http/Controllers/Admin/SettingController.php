<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        $currentGroup = $request->input('group', 'general');

        $groups = [
            'general' => ['title' => 'General Settings', 'icon' => 'fa-gear'],
            'api' => ['title' => 'API Configuration', 'icon' => 'fa-code'],
            'razorpay' => ['title' => 'Razorpay Gateway', 'icon' => 'fa-credit-card'],
            'otp_smtp' => ['title' => 'OTP & SMTP Mail', 'icon' => 'fa-envelope'],
            'location' => ['title' => 'Location & Radius', 'icon' => 'fa-map-location-dot'],
            'matching' => ['title' => 'Matching Algorithm', 'icon' => 'fa-sliders'],
            'swipes' => ['title' => 'Swipe Limits & Stars', 'icon' => 'fa-fire'],
            'verification' => ['title' => 'Profile Verification', 'icon' => 'fa-shield-halved'],
            'security' => ['title' => 'Security & Privacy', 'icon' => 'fa-lock'],
        ];

        $settings = Setting::where('group', $currentGroup)->get();

        return view('admin.settings.index', compact('settings', 'groups', 'currentGroup'));
    }

    public function update(Request $request)
    {
        $settingsData = $request->except(['_token', 'group']);
        $group = $request->input('group', 'general');

        foreach ($settingsData as $key => $value) {
            Setting::set($key, $value, $group);
        }

        ActivityLog::record(Auth::guard('admin')->id(), 'update_settings', 'Setting', null, [
            'group' => $group,
            'updated_keys' => array_keys($settingsData),
        ]);

        return back()->with('success', 'Settings updated successfully.');
    }
}
