<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::withCount('subscriptions')
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('admin.subscriptions.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.subscriptions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'duration_days' => 'required|integer|min:1',
            'daily_swipe_limit' => 'required|integer', // -1 = unlimited
            'daily_star_allowance' => 'required|integer|min:0',
            'badge_color' => 'required|string|max:20',
            'can_see_likes' => 'nullable|boolean',
            'can_rewind' => 'nullable|boolean',
            'can_boost' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $slug = Str::slug($request->name);
        $count = SubscriptionPlan::where('slug', 'like', "{$slug}%")->count();
        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        $plan = SubscriptionPlan::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'price' => $request->price,
            'currency' => $request->currency,
            'duration_days' => $request->duration_days,
            'daily_swipe_limit' => $request->daily_swipe_limit,
            'daily_star_allowance' => $request->daily_star_allowance,
            'badge_color' => $request->badge_color,
            'can_see_likes' => $request->boolean('can_see_likes'),
            'can_rewind' => $request->boolean('can_rewind'),
            'can_boost' => $request->boolean('can_boost'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        ActivityLog::record(Auth::guard('admin')->id(), 'create_subscription_plan', 'SubscriptionPlan', $plan->id, ['name' => $plan->name]);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', "Plan '{$plan->name}' created successfully.");
    }

    public function edit(int $id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        return view('admin.subscriptions.edit', compact('plan'));
    }

    public function update(Request $request, int $id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'duration_days' => 'required|integer|min:1',
            'daily_swipe_limit' => 'required|integer',
            'daily_star_allowance' => 'required|integer|min:0',
            'badge_color' => 'required|string|max:20',
            'can_see_likes' => 'nullable|boolean',
            'can_rewind' => 'nullable|boolean',
            'can_boost' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $plan->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'currency' => $request->currency,
            'duration_days' => $request->duration_days,
            'daily_swipe_limit' => $request->daily_swipe_limit,
            'daily_star_allowance' => $request->daily_star_allowance,
            'badge_color' => $request->badge_color,
            'can_see_likes' => $request->boolean('can_see_likes'),
            'can_rewind' => $request->boolean('can_rewind'),
            'can_boost' => $request->boolean('can_boost'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        ActivityLog::record(Auth::guard('admin')->id(), 'update_subscription_plan', 'SubscriptionPlan', $plan->id, ['name' => $plan->name]);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', "Plan '{$plan->name}' updated successfully.");
    }

    public function toggleActive(int $id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $plan->is_active = !$plan->is_active;
        $plan->save();

        ActivityLog::record(Auth::guard('admin')->id(), 'toggle_plan_status', 'SubscriptionPlan', $plan->id, ['is_active' => $plan->is_active]);

        return back()->with('success', "Plan status toggled successfully.");
    }

    /**
     * User subscriptions list.
     */
    public function userSubscriptions(Request $request)
    {
        $status = $request->input('status', 'all');

        $query = UserSubscription::with(['user', 'plan', 'payment'])
            ->latest('id');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $subscriptions = $query->paginate(25)->withQueryString();

        return view('admin.subscriptions.users', compact('subscriptions', 'status'));
    }

    /**
     * Extend user subscription days.
     */
    public function extend(Request $request, int $id)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $sub = UserSubscription::findOrFail($id);
        $baseDate = $sub->expires_at->isFuture() ? $sub->expires_at : Carbon::now();
        $newExpiry = $baseDate->copy()->addDays($request->days);

        $sub->update([
            'expires_at' => $newExpiry,
            'status' => 'active',
        ]);

        $sub->user->update([
            'is_premium' => true,
            'premium_expires_at' => $newExpiry,
        ]);

        ActivityLog::record(Auth::guard('admin')->id(), 'extend_user_subscription', 'UserSubscription', $sub->id, [
            'days' => $request->days,
            'new_expiry' => $newExpiry->toDateTimeString(),
        ]);

        return back()->with('success', "Subscription extended by {$request->days} days. New expiration: {$newExpiry->format('d M Y')}.");
    }

    /**
     * Cancel user subscription.
     */
    public function cancel(int $id)
    {
        $sub = UserSubscription::findOrFail($id);
        $sub->update(['status' => 'cancelled']);

        $sub->user->update([
            'is_premium' => false,
            'premium_expires_at' => null,
        ]);

        ActivityLog::record(Auth::guard('admin')->id(), 'cancel_user_subscription', 'UserSubscription', $sub->id);

        return back()->with('info', "Subscription for {$sub->user->name} has been cancelled.");
    }
}
