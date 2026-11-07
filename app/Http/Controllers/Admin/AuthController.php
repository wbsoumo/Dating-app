<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $admin = Auth::guard('admin')->user();

            if ($admin->status !== 'active') {
                Auth::guard('admin')->logout();
                return back()->withErrors(['email' => 'Your administrator account has been disabled.']);
            }

            $admin->update([
                'last_login_at' => Carbon::now(),
                'last_login_ip' => $request->ip(),
            ]);

            ActivityLog::record($admin->id, 'admin_login', 'Admin', $admin->id, ['ip' => $request->ip()]);

            return redirect()->intended(route('admin.dashboard'))
                ->with('success', "Welcome back, {$admin->name}!");
        }

        return back()->withErrors([
            'email' => 'The provided administrator credentials do not match our records.',
        ])->withInput($request->only('email', 'remember'));
    }

    public function logout(Request $request)
    {
        $adminId = Auth::guard('admin')->id();
        ActivityLog::record($adminId, 'admin_logout', 'Admin', $adminId);

        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('info', 'You have been logged out.');
    }
}
