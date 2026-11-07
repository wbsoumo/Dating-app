<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends BaseApiController
{
    /**
     * List all available active subscription plans.
     */
    public function listPlans(): JsonResponse
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        return $this->sendResponse($plans, 'Subscription plans retrieved.');
    }

    /**
     * Get current user's active subscription status.
     */
    public function getCurrentSubscription(Request $request): JsonResponse
    {
        $user = $request->user();
        $sub = $user->activeSubscription;

        if (!$sub || !$sub->isCurrent()) {
            return $this->sendResponse([
                'is_active' => false,
                'plan' => null,
                'starts_at' => null,
                'expires_at' => null,
            ], 'No active premium subscription.');
        }

        return $this->sendResponse([
            'is_active' => true,
            'subscription_id' => $sub->id,
            'plan' => $sub->plan,
            'starts_at' => $sub->starts_at->toIso8601String(),
            'expires_at' => $sub->expires_at->toIso8601String(),
            'auto_renew' => (bool) $sub->auto_renew,
        ], 'Active subscription retrieved.');
    }

    /**
     * Get user's subscription history.
     */
    public function getHistory(Request $request): JsonResponse
    {
        $user = $request->user();

        $history = UserSubscription::with(['plan', 'payment'])
            ->where('user_id', $user->id)
            ->latest('id')
            ->paginate(15);

        return $this->sendResponse($history, 'Subscription history retrieved.');
    }
}
