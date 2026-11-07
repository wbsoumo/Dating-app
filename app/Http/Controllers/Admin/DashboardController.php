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
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        // 1. User Statistics
        $totalUsers = User::count();
        $maleUsers = User::where('gender', 'male')->count();
        $femaleUsers = User::where('gender', 'female')->count();
        $otherUsers = $totalUsers - ($maleUsers + $femaleUsers);

        $activeUsers = User::where('status', 'active')->count();
        $inactiveUsers = $totalUsers - $activeUsers;

        $verifiedUsers = User::where('is_verified', true)->count();
        $unverifiedUsers = $totalUsers - $verifiedUsers;

        $demoUsers = User::where('is_demo', true)->count();

        // 2. Swipes & Matches
        $totalSwipes = Swipe::count();
        $totalLikes = Swipe::whereIn('type', ['like', 'star'])->count();
        $totalStars = Swipe::where('type', 'star')->count();
        $totalMatches = UserMatch::where('status', 'active')->count();

        // 3. Financials & Subscriptions
        $totalRevenue = Payment::where('status', 'captured')->sum('amount');
        $todayRevenue = Payment::where('status', 'captured')->whereDate('created_at', $today)->sum('amount');
        $thisMonthRevenue = Payment::where('status', 'captured')->where('created_at', '>=', $thisMonth)->sum('amount');
        $activeSubscriptions = UserSubscription::where('status', 'active')->where('expires_at', '>', now())->count();
        $pendingReports = UserReport::where('status', 'pending')->count();

        // 4. Daily Analytics Chart Data (Last 7 Days)
        $registrationTrends = [];
        $revenueTrends = [];
        $dateLabels = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateLabel = $date->format('d M');
            $dateLabels[] = $dateLabel;

            $registrationTrends[] = User::whereDate('created_at', $date)->count();
            $revenueTrends[] = (float) Payment::where('status', 'captured')
                ->whereDate('created_at', $date)
                ->sum('amount');
        }

        // 5. Recent Registrations & Transactions
        $recentUsers = User::with(['profile', 'location', 'photos'])
            ->latest('id')
            ->limit(8)
            ->get();

        $recentTransactions = Payment::with(['user'])
            ->latest('id')
            ->limit(8)
            ->get();

        $currency = Setting::get('currency_symbol', '₹');

        return view('admin.dashboard', compact(
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
            'revenueTrends',
            'recentUsers',
            'recentTransactions',
            'currency'
        ));
    }
}
