<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DiscoveryController;
use App\Http\Controllers\Api\V1\DocsController;
use App\Http\Controllers\Api\V1\MatchController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ReportBlockController;
use App\Http\Controllers\Api\V1\StarController;
use App\Http\Controllers\Api\V1\SubscriptionController;
use App\Http\Controllers\Api\V1\SwipeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Dating Platform (v1)
|--------------------------------------------------------------------------
*/

// Documentation & Postman Export
Route::get('/docs', [DocsController::class, 'index'])->name('api.docs');
Route::get('/docs/postman', [DocsController::class, 'exportPostman'])->name('api.docs.postman');

// V1 Prefix Group
Route::prefix('v1')->group(function () {

    // Webhook - Razorpay (Public webhook endpoint with signature header)
    Route::post('/payments/webhook', [PaymentController::class, 'webhook']);

    // Public Auth Routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/send-otp', [AuthController::class, 'sendOtp']);
        Route::post('/verify-otp', [AuthController::class, 'verifyOtpLogin']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    // Public Master Data Catalogues
    Route::get('/interests', [ProfileController::class, 'listAllInterests']);
    Route::get('/subscriptions/plans', [SubscriptionController::class, 'listPlans']);
    Route::get('/stars/packages', [StarController::class, 'listPackages']);

    // Authenticated Routes (Requires Bearer Sanctum Token)
    Route::middleware(['auth:sanctum'])->group(function () {

        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Profile Management
        Route::prefix('user')->group(function () {
            Route::get('/profile', [ProfileController::class, 'getProfile']);
            Route::put('/profile', [ProfileController::class, 'updateProfile']);
            Route::post('/photos', [ProfileController::class, 'uploadPhoto']);
            Route::delete('/photos/{id}', [ProfileController::class, 'deletePhoto']);
            Route::put('/photos/{id}/primary', [ProfileController::class, 'setPrimaryPhoto']);
            Route::post('/interests', [ProfileController::class, 'syncInterests']);
            Route::put('/preferences', [ProfileController::class, 'updatePreferences']);
            Route::post('/location', [ProfileController::class, 'updateLocation']);
            Route::post('/verify-request', [ProfileController::class, 'requestVerification']);
            Route::post('/deactivate', [ProfileController::class, 'deactivateAccount']);
            Route::delete('/delete', [ProfileController::class, 'deleteAccount']);

            // Safety - Block & Report
            Route::post('/report', [ReportBlockController::class, 'reportUser']);
            Route::post('/block', [ReportBlockController::class, 'blockUser']);
            Route::get('/blocked', [ReportBlockController::class, 'listBlocked']);
            Route::post('/unblock', [ReportBlockController::class, 'unblockUser']);
        });

        // Location-Based Matching Engine & Discovery
        Route::prefix('matches')->group(function () {
            Route::get('/recommendations', [DiscoveryController::class, 'getRecommendations']);
            Route::get('/nearby', [DiscoveryController::class, 'getNearby']);
            Route::get('/', [MatchController::class, 'listMatches']);
            Route::get('/{id}', [MatchController::class, 'getMatch']);
            Route::post('/{id}/unmatch', [MatchController::class, 'unmatch']);
        });

        // Swipes & Limits
        Route::prefix('swipes')->group(function () {
            Route::post('/', [SwipeController::class, 'swipe']);
            Route::get('/limits', [SwipeController::class, 'getLimits']);
            Route::get('/history', [SwipeController::class, 'getHistory']);
            Route::get('/likes-received', [SwipeController::class, 'getLikesReceived']);
        });

        // Subscriptions
        Route::prefix('subscriptions')->group(function () {
            Route::get('/current', [SubscriptionController::class, 'getCurrentSubscription']);
            Route::get('/history', [SubscriptionController::class, 'getHistory']);
        });

        // Stars & Wallet
        Route::prefix('stars')->group(function () {
            Route::get('/wallet', [StarController::class, 'getWallet']);
            Route::post('/send', [StarController::class, 'sendStar']);
        });

        // Razorpay Payments
        Route::prefix('payments')->group(function () {
            Route::post('/create-order', [PaymentController::class, 'createOrder']);
            Route::post('/verify', [PaymentController::class, 'verifyPayment']);
            Route::get('/history', [PaymentController::class, 'getHistory']);
        });

        // Notifications
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'listNotifications']);
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
        });
    });
});
