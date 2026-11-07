<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Setting;
use App\Models\Swipe;
use App\Models\User;
use App\Models\UserMatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SwipeService
{
    /**
     * Process a swipe action.
     *
     * @return array [ 'success' => bool, 'is_match' => bool, 'match' => ?UserMatch, 'message' => string ]
     */
    public function swipe(User $user, int $targetUserId, string $type): array
    {
        if ($user->id === $targetUserId) {
            throw new InvalidArgumentException('You cannot swipe on your own profile.');
        }

        if (!in_array($type, ['like', 'dislike', 'star'])) {
            throw new InvalidArgumentException('Invalid swipe type. Allowed: like, dislike, star.');
        }

        $targetUser = User::where('id', $targetUserId)->where('status', 'active')->first();
        if (!$targetUser) {
            throw new InvalidArgumentException('Target profile not found or inactive.');
        }

        // Prevent duplicate swipes
        $existingSwipe = Swipe::where('user_id', $user->id)
            ->where('target_user_id', $targetUserId)
            ->first();

        if ($existingSwipe) {
            throw new InvalidArgumentException('You have already swiped on this profile.');
        }

        // Check daily swipe limits for regular likes/dislikes
        if ($type !== 'star' && !$user->canSwipeToday()) {
            return [
                'success' => false,
                'is_match' => false,
                'match' => null,
                'message' => 'Daily swipe limit reached. Upgrade to premium for unlimited swipes!',
                'code' => 'LIMIT_REACHED',
            ];
        }

        // Handle Star / Super Like deduction
        if ($type === 'star') {
            $starCost = (int) Setting::get('super_like_star_cost', 1);
            $wallet = $user->getOrCreateWallet();

            if ($wallet->balance < $starCost) {
                return [
                    'success' => false,
                    'is_match' => false,
                    'match' => null,
                    'message' => 'Insufficient stars to send a Super Like. Please purchase a star pack!',
                    'code' => 'INSUFFICIENT_STARS',
                ];
            }

            $wallet->debit($starCost, "Sent Super Like to {$targetUser->name}", 'swipe_spent', (string) $targetUserId);
        }

        return DB::transaction(function () use ($user, $targetUser, $type) {
            // Record the swipe
            $swipe = Swipe::create([
                'user_id' => $user->id,
                'target_user_id' => $targetUser->id,
                'type' => $type,
            ]);

            $isMatch = false;
            $matchRecord = null;

            // If it's a positive swipe (like or star), check if target user already liked or starred current user
            if (in_array($type, ['like', 'star'])) {
                $reciprocalSwipe = Swipe::where('user_id', $targetUser->id)
                    ->where('target_user_id', $user->id)
                    ->whereIn('type', ['like', 'star'])
                    ->first();

                if ($reciprocalSwipe) {
                    $isMatch = true;
                    $user1Id = min($user->id, $targetUser->id);
                    $user2Id = max($user->id, $targetUser->id);

                    $matchRecord = UserMatch::firstOrCreate(
                        ['user1_id' => $user1Id, 'user2_id' => $user2Id],
                        [
                            'status' => 'active',
                            'matched_at' => Carbon::now(),
                        ]
                    );

                    // Notify both users
                    Notification::create([
                        'user_id' => $user->id,
                        'title' => 'It\'s a Match! 🎉',
                        'body' => "You and {$targetUser->name} liked each other. Say hello!",
                        'type' => 'match',
                        'data' => ['match_id' => $matchRecord->id, 'partner_id' => $targetUser->id],
                    ]);

                    Notification::create([
                        'user_id' => $targetUser->id,
                        'title' => 'It\'s a Match! 🎉',
                        'body' => "You and {$user->name} liked each other. Say hello!",
                        'type' => 'match',
                        'data' => ['match_id' => $matchRecord->id, 'partner_id' => $user->id],
                    ]);
                } else {
                    // Send like / star notification to target user
                    $actionText = ($type === 'star') ? 'sent you a Super Like ⭐' : 'liked your profile ❤️';
                    Notification::create([
                        'user_id' => $targetUser->id,
                        'title' => 'Someone likes you!',
                        'body' => ($type === 'star')
                            ? "{$user->name} sent you a Super Like!"
                            : "Someone new liked your profile. Swipe to find out!",
                        'type' => $type,
                        'data' => ['sender_id' => $user->id],
                    ]);
                }
            }

            return [
                'success' => true,
                'is_match' => $isMatch,
                'match' => $matchRecord ? [
                    'id' => $matchRecord->id,
                    'matched_at' => $matchRecord->matched_at->toIso8601String(),
                    'partner' => [
                        'id' => $targetUser->id,
                        'name' => $targetUser->name,
                        'age' => $targetUser->age,
                        'avatar' => $targetUser->photos->where('is_primary', true)->first()?->url ?? $targetUser->photos->first()?->url,
                    ],
                ] : null,
                'message' => $isMatch ? 'It\'s a match!' : 'Swipe recorded successfully.',
                'remaining_swipes' => $user->getSwipesRemainingToday(),
                'stars_balance' => $user->getOrCreateWallet()->balance,
            ];
        });
    }

    /**
     * Get user's current swipe quota and balance.
     */
    public function getLimits(User $user): array
    {
        $limit = $user->getDailySwipeLimit();
        $used = $user->getSwipesUsedToday();
        $wallet = $user->getOrCreateWallet();

        return [
            'is_premium' => $user->hasActivePremium(),
            'daily_limit' => $limit, // -1 = unlimited
            'used_today' => $used,
            'remaining_today' => ($limit === -1) ? -1 : max(0, $limit - $used),
            'can_swipe' => ($limit === -1) || ($limit > $used),
            'stars_balance' => $wallet->balance,
            'super_like_cost' => (int) Setting::get('super_like_star_cost', 1),
        ];
    }
}
