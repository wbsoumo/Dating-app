<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Swipe;
use App\Models\User;
use App\Models\UserMatch;
use App\Models\UserReport;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $sevenDaysAgo = Carbon::today()->subDays(6)->startOfDay();

        // High-Performance Cached Metrics (cached for 15 seconds)
        $metrics = Cache::remember('admin_dashboard_metrics', 15, function () use ($today, $thisMonth, $sevenDaysAgo) {
            // 1. Single-pass User Aggregates
            try {
                $userStats = DB::table('users')->selectRaw("
                    COUNT(*) as total,
                    COALESCE(SUM(CASE WHEN gender = 'male' THEN 1 ELSE 0 END), 0) as male,
                    COALESCE(SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END), 0) as female,
                    COALESCE(SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END), 0) as active,
                    COALESCE(SUM(CASE WHEN is_verified = 1 THEN 1 ELSE 0 END), 0) as verified,
                    COALESCE(SUM(CASE WHEN is_demo = 1 THEN 1 ELSE 0 END), 0) as demo
                ")->first();
            } catch (\Throwable $e) {
                $userStats = (object) ['total' => 0, 'male' => 0, 'female' => 0, 'active' => 0, 'verified' => 0, 'demo' => 0];
            }

            $totalUsers = (int) ($userStats->total ?? 0);
            $maleUsers = (int) ($userStats->male ?? 0);
            $femaleUsers = (int) ($userStats->female ?? 0);
            $otherUsers = max(0, $totalUsers - ($maleUsers + $femaleUsers));
            $activeUsers = (int) ($userStats->active ?? 0);
            $inactiveUsers = max(0, $totalUsers - $activeUsers);
            $verifiedUsers = (int) ($userStats->verified ?? 0);
            $unverifiedUsers = max(0, $totalUsers - $verifiedUsers);
            $demoUsers = (int) ($userStats->demo ?? 0);

            // 2. Single-pass Swipe Aggregates
            try {
                $swipeStats = DB::table('swipes')->selectRaw("
                    COUNT(*) as total,
                    COALESCE(SUM(CASE WHEN type IN ('like', 'star') THEN 1 ELSE 0 END), 0) as likes,
                    COALESCE(SUM(CASE WHEN type = 'star' THEN 1 ELSE 0 END), 0) as stars
                ")->first();
                $totalSwipes = (int) ($swipeStats->total ?? 0);
                $totalLikes = (int) ($swipeStats->likes ?? 0);
                $totalStars = (int) ($swipeStats->stars ?? 0);
            } catch (\Throwable $e) {
                $totalSwipes = 0; $totalLikes = 0; $totalStars = 0;
            }

            // 3. Matches
            try {
                $totalMatches = (int) DB::table('matches')->where('status', 'active')->count();
            } catch (\Throwable $e) {
                $totalMatches = 0;
            }

            // 4. Single-pass Payment Aggregates
            try {
                $paymentStats = DB::table('payments')
                    ->where('status', 'captured')
                    ->selectRaw("
                        COALESCE(SUM(amount), 0) as total,
                        COALESCE(SUM(CASE WHEN created_at >= ? THEN amount ELSE 0 END), 0) as today,
                        COALESCE(SUM(CASE WHEN created_at >= ? THEN amount ELSE 0 END), 0) as this_month
                    ", [$today->copy()->startOfDay(), $thisMonth->copy()->startOfMonth()])
                    ->first();
                $totalRevenue = (float) ($paymentStats->total ?? 0);
                $todayRevenue = (float) ($paymentStats->today ?? 0);
                $thisMonthRevenue = (float) ($paymentStats->this_month ?? 0);
            } catch (\Throwable $e) {
                $totalRevenue = 0; $todayRevenue = 0; $thisMonthRevenue = 0;
            }

            // 5. Active Subscriptions & Reports
            try {
                $activeSubscriptions = Schema::hasTable('user_subscriptions')
                    ? (int) DB::table('user_subscriptions')->where('status', 'active')->where('expires_at', '>', Carbon::now())->count()
                    : 0;
            } catch (\Throwable $e) {
                $activeSubscriptions = 0;
            }

            try {
                $pendingReports = Schema::hasTable('user_reports')
                    ? (int) DB::table('user_reports')->where('status', 'pending')->count()
                    : 0;
            } catch (\Throwable $e) {
                $pendingReports = 0;
            }

            // 6. 7-Day Growth Trends (Two Single Grouped Queries, NOT 14 queries in a loop)
            try {
                $dailyUsers = DB::table('users')
                    ->where('created_at', '>=', $sevenDaysAgo)
                    ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as dt, COUNT(*) as cnt")
                    ->groupBy('dt')
                    ->pluck('cnt', 'dt')
                    ->toArray();
            } catch (\Throwable $e) {
                $dailyUsers = [];
            }

            try {
                $dailyRevenue = DB::table('payments')
                    ->where('status', 'captured')
                    ->where('created_at', '>=', $sevenDaysAgo)
                    ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as dt, SUM(amount) as amt")
                    ->groupBy('dt')
                    ->pluck('amt', 'dt')
                    ->toArray();
            } catch (\Throwable $e) {
                $dailyRevenue = [];
            }

            $dateLabels = [];
            $registrationTrends = [];
            $revenueTrends = [];

            for ($i = 6; $i >= 0; $i--) {
                $d = Carbon::today()->subDays($i);
                $ymd = $d->format('Y-m-d');
                $dateLabels[] = $d->format('d M');
                $registrationTrends[] = (int) ($dailyUsers[$ymd] ?? 0);
                $revenueTrends[] = (float) ($dailyRevenue[$ymd] ?? 0);
            }

            return compact(
                'totalUsers',
                'maleUsers',
                'femaleUsers',
                'otherUsers',
                'activeUsers',
                'inactiveUsers',
                'verifiedUsers',
                'unverifiedUsers',
                'demoUsers',
                'totalSwipes',
                'totalLikes',
                'totalStars',
                'totalMatches',
                'totalRevenue',
                'todayRevenue',
                'thisMonthRevenue',
                'activeSubscriptions',
                'pendingReports',
                'dateLabels',
                'registrationTrends',
                'revenueTrends'
            );
        });

        // 7. Recent Registrations & Transactions (optimized queries)
        try {
            $recentUsers = User::with(['profile', 'location', 'photos'])
                ->latest('id')
                ->limit(8)
                ->get();
        } catch (\Throwable $e) {
            $recentUsers = collect();
        }

        try {
            $recentTransactions = Payment::with(['user'])
                ->latest('id')
                ->limit(8)
                ->get();
        } catch (\Throwable $e) {
            $recentTransactions = collect();
        }

        $currency = Setting::get('currency_symbol', '₹');

        return view('admin.dashboard', array_merge($metrics, compact(
            'recentUsers',
            'recentTransactions',
            'currency'
        )));
    }
}
