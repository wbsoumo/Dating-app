<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    /**
     * Auto-initialize database tables and seeders if not yet migrated.
     */
    protected function ensureDatabaseMigrated(): void
    {
        try {
            if (!Schema::hasTable('activity_logs') || !Schema::hasTable('settings')) {
                @ini_set('memory_limit', '512M');
                @set_time_limit(300);

                Artisan::call('migrate', ['--force' => true]);
                Artisan::call('db:seed', [
                    '--class' => 'Database\Seeders\AdminSeeder',
                    '--force' => true,
                ]);
                Artisan::call('db:seed', [
                    '--class' => 'Database\Seeders\SettingSeeder',
                    '--force' => true,
                ]);
                Artisan::call('db:seed', [
                    '--class' => 'Database\Seeders\SubscriptionPlanSeeder',
                    '--force' => true,
                ]);
                Artisan::call('db:seed', [
                    '--class' => 'Database\Seeders\StarPackageSeeder',
                    '--force' => true,
                ]);
                Artisan::call('db:seed', [
                    '--class' => 'Database\Seeders\InterestSeeder',
                    '--force' => true,
                ]);
            }
        } catch (\Throwable $e) {
            // Ignore and continue
        }
    }

    public function showLogin()
    {
        $this->ensureDatabaseMigrated();

        try {
            if (Auth::guard('admin')->check()) {
                return redirect()->route('admin.dashboard');
            }
        } catch (\Throwable $e) {
            // Guard check fallback
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $this->ensureDatabaseMigrated();

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
