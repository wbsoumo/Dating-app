<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Database\Seeders\DemoActivitySeeder;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DemoDataController extends Controller
{
    public function index()
    {
        $demoUserCount = User::where('is_demo', true)->count();
        $realUserCount = User::where('is_demo', false)->count();

        $maleDemoCount = User::where('is_demo', true)->where('gender', 'male')->count();
        $femaleDemoCount = User::where('is_demo', true)->where('gender', 'female')->count();

        return view('admin.demo_data.index', compact(
            'demoUserCount',
            'realUserCount',
            'maleDemoCount',
            'femaleDemoCount'
        ));
    }

    public function generate(Request $request)
    {
        $count = (int) $request->input('count', 2020);
        $count = min(3000, max(10, $count));

        try {
            $seeder = new DemoUserSeeder();
            $seeder->run($count);

            ActivityLog::record(Auth::guard('admin')->id(), 'generate_demo_data', null, null, ['count' => $count]);

            return back()->with('success', "Successfully generated {$count} Indian demo profiles with locations, photos, and preferences!");
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Demo generation error: ' . $e->getMessage()]);
        }
    }

    public function generateActivity()
    {
        try {
            $seeder = new DemoActivitySeeder();
            $seeder->run();

            return back()->with('success', "Sample swipes, matches, and transactions generated between demo accounts.");
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Demo activity error: ' . $e->getMessage()]);
        }
    }

    public function purge(Request $request)
    {
        $confirm = $request->input('confirm_purge');
        if ($confirm !== 'DELETE DEMO DATA') {
            return back()->withErrors(['confirm_purge' => 'Please type exactly "DELETE DEMO DATA" to confirm purging.']);
        }

        try {
            DB::transaction(function () {
                $demoUserIds = User::where('is_demo', true)->pluck('id')->toArray();

                if (!empty($demoUserIds)) {
                    // Force delete demo users (cascades to profiles, locations, photos, preferences, wallets, etc.)
                    User::whereIn('id', $demoUserIds)->forceDelete();
                }
            });

            ActivityLog::record(Auth::guard('admin')->id(), 'purge_demo_data');

            return back()->with('success', 'All demo profiles, photos, and associated dummy data have been completely removed. Your system is now clean for production!');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Purge error: ' . $e->getMessage()]);
        }
    }
}
