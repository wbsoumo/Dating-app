<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DemoDataController;
use App\Http\Controllers\Admin\MatchController as AdminMatchController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\StarController as AdminStarController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\VerificationController as AdminVerificationController;
use App\Http\Controllers\InstallerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Dating Platform Backend & AdminLTE 3 Panel
|--------------------------------------------------------------------------
*/

// Root redirect
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Database Table Checker & Auto-Importer
Route::get('/check-tables', function () {
    require_once public_path('check_tables.php');
    exit;
});

// Secure One-Click Database Migrator & Seeder via Browser
Route::get('/migrate-db', function () {
    $key = request('key');
    if ($key !== 'taskbazi_dating_deploy_2026') {
        abort(403, 'Unauthorized. Please provide valid deployment key.');
    }

    @ini_set('memory_limit', '512M');
    @set_time_limit(300);

    $results = [];

    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $results['migrate'] = trim(\Illuminate\Support\Facades\Artisan::output());

        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => 'Database\Seeders\AdminSeeder',
            '--force' => true,
        ]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => 'Database\Seeders\SettingSeeder',
            '--force' => true,
        ]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => 'Database\Seeders\SubscriptionPlanSeeder',
            '--force' => true,
        ]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => 'Database\Seeders\StarPackageSeeder',
            '--force' => true,
        ]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => 'Database\Seeders\InterestSeeder',
            '--force' => true,
        ]);

        $results['seed'] = 'All master seeders completed successfully!';
    } catch (\Throwable $e) {
        $results['error'] = $e->getMessage();
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Database initialized successfully!',
        'details' => $results,
        'login_url' => url('/admin/login'),
    ], 200, [], JSON_PRETTY_PRINT);
});

// Automated Web Installer (Protected one-time URL)
Route::prefix('install')->group(function () {
    Route::get('/', [InstallerController::class, 'index'])->name('installer.index');
    Route::get('/database', [InstallerController::class, 'showDatabaseForm'])->name('installer.database');
    Route::post('/database', [InstallerController::class, 'processDatabase'])->name('installer.database.process');
    Route::get('/migrate', [InstallerController::class, 'showMigrate'])->name('installer.migrate');
    Route::post('/migrate', [InstallerController::class, 'processMigrate'])->name('installer.migrate.process');
    Route::get('/admin', [InstallerController::class, 'showAdmin'])->name('installer.admin');
    Route::post('/admin', [InstallerController::class, 'processAdmin'])->name('installer.admin.process');
});

// Admin Panel Routes
Route::prefix('admin')->group(function () {

    // Guest Auth Routes
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::get('/auth/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    // Authenticated Admin Guard Routes
    Route::middleware(['auth:admin'])->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        // User Management
        Route::prefix('users')->group(function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('admin.users.index');
            Route::get('/{id}', [AdminUserController::class, 'show'])->name('admin.users.show');
            Route::get('/{id}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
            Route::put('/{id}', [AdminUserController::class, 'update'])->name('admin.users.update');
            Route::delete('/{id}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
            Route::post('/{id}/toggle-verify', [AdminUserController::class, 'toggleVerification'])->name('admin.users.toggle-verify');
            Route::post('/{id}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
            Route::post('/{id}/adjust-stars', [AdminUserController::class, 'adjustStars'])->name('admin.users.adjust-stars');
        });

        // Verification Requests Queue
        Route::prefix('verifications')->group(function () {
            Route::get('/', [AdminVerificationController::class, 'index'])->name('admin.verifications.index');
            Route::post('/{id}/approve', [AdminVerificationController::class, 'approve'])->name('admin.verifications.approve');
            Route::post('/{id}/reject', [AdminVerificationController::class, 'reject'])->name('admin.verifications.reject');
        });

        // Matches & Swipes
        Route::prefix('matches')->group(function () {
            Route::get('/', [AdminMatchController::class, 'index'])->name('admin.matches.index');
            Route::get('/swipes', [AdminMatchController::class, 'swipes'])->name('admin.matches.swipes');
        });

        // Subscription Plans & User Subscriptions
        Route::prefix('subscriptions')->group(function () {
            Route::get('/', [AdminSubscriptionController::class, 'index'])->name('admin.subscriptions.index');
            Route::get('/create', [AdminSubscriptionController::class, 'create'])->name('admin.subscriptions.create');
            Route::post('/', [AdminSubscriptionController::class, 'store'])->name('admin.subscriptions.store');
            Route::get('/{id}/edit', [AdminSubscriptionController::class, 'edit'])->name('admin.subscriptions.edit');
            Route::put('/{id}', [AdminSubscriptionController::class, 'update'])->name('admin.subscriptions.update');
            Route::post('/{id}/toggle-active', [AdminSubscriptionController::class, 'toggleActive'])->name('admin.subscriptions.toggle-active');

            // Subscribers
            Route::get('/users/roster', [AdminSubscriptionController::class, 'userSubscriptions'])->name('admin.subscriptions.users');
            Route::post('/users/{id}/extend', [AdminSubscriptionController::class, 'extend'])->name('admin.subscriptions.extend');
            Route::post('/users/{id}/cancel', [AdminSubscriptionController::class, 'cancel'])->name('admin.subscriptions.cancel');
        });

        // Star Packages & Wallets
        Route::prefix('stars')->group(function () {
            Route::get('/', [AdminStarController::class, 'index'])->name('admin.stars.index');
            Route::get('/create', [AdminStarController::class, 'create'])->name('admin.stars.create');
            Route::post('/', [AdminStarController::class, 'store'])->name('admin.stars.store');
            Route::get('/{id}/edit', [AdminStarController::class, 'edit'])->name('admin.stars.edit');
            Route::put('/{id}', [AdminStarController::class, 'update'])->name('admin.stars.update');
            Route::post('/{id}/toggle-active', [AdminStarController::class, 'toggleActive'])->name('admin.stars.toggle-active');
            Route::get('/transactions/log', [AdminStarController::class, 'transactions'])->name('admin.stars.transactions');
            Route::get('/wallets/overview', [AdminStarController::class, 'wallets'])->name('admin.stars.wallets');
        });

        // Razorpay Financial Management
        Route::prefix('payments')->group(function () {
            Route::get('/', [AdminPaymentController::class, 'index'])->name('admin.payments.index');
            Route::get('/export/csv', [AdminPaymentController::class, 'exportCsv'])->name('admin.payments.export-csv');
            Route::get('/{id}', [AdminPaymentController::class, 'show'])->name('admin.payments.show');
            Route::post('/{id}/refund', [AdminPaymentController::class, 'processRefund'])->name('admin.payments.refund');
        });

        // Safety & Moderation (Reports)
        Route::prefix('reports')->group(function () {
            Route::get('/', [AdminReportController::class, 'index'])->name('admin.reports.index');
            Route::post('/{id}/resolve', [AdminReportController::class, 'resolve'])->name('admin.reports.resolve');
        });

        // Global Configuration & Settings
        Route::prefix('settings')->group(function () {
            Route::get('/', [AdminSettingController::class, 'index'])->name('admin.settings.index');
            Route::post('/update', [AdminSettingController::class, 'update'])->name('admin.settings.update');
        });

        // Demo Data Manager
        Route::prefix('demo-data')->group(function () {
            Route::get('/', [DemoDataController::class, 'index'])->name('admin.demo-data.index');
            Route::post('/generate', [DemoDataController::class, 'generate'])->name('admin.demo-data.generate');
            Route::post('/activity', [DemoDataController::class, 'activity'])->name('admin.demo-data.activity');
            Route::post('/purge', [DemoDataController::class, 'purge'])->name('admin.demo-data.purge');
        });

        // Activity Audit Logs
        Route::prefix('logs')->group(function () {
            Route::get('/', [ActivityLogController::class, 'index'])->name('admin.logs.index');
        });
    });
});
