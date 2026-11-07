<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Swipe;
use App\Models\User;
use App\Services\SwipeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SwipeController extends BaseApiController
{
    protected SwipeService $swipeService;

    public function __construct(SwipeService $swipeService)
    {
        $this->swipeService = $swipeService;
    }

    /**
     * Submit a swipe action (like, dislike, star).
     */
    public function swipe(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'target_user_id' => 'required|exists:users,id',
            'type' => 'required|in:like,dislike,star',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        try {
            $user = $request->user();
            $result = $this->swipeService->swipe(
                $user,
                (int) $request->target_user_id,
                $request->type
            );

            if (!$result['success']) {
                return $this->sendError($result['message'], ['code' => $result['code'] ?? 'SWIPE_FAILED'], 400);
            }

            return $this->sendResponse($result, $result['message']);
        } catch (\Throwable $e) {
            return $this->sendError($e->getMessage(), null, 400);
        }
    }

    /**
     * Get user's swipe quota and limits.
     */
    public function getLimits(Request $request): JsonResponse
    {
        $user = $request->user();
        $limits = $this->swipeService->getLimits($user);

        return $this->sendResponse($limits, 'Limits retrieved.');
    }

    /**
     * View history of swipes made by authenticated user.
     */
    public function getHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        $type = $request->query('type'); // like, dislike, star

        $query = Swipe::with(['targetUser.photos', 'targetUser.profile'])
            ->where('user_id', $user->id)
            ->latest('id');

        if ($type && in_array($type, ['like', 'dislike', 'star'])) {
            $query->where('type', $type);
        }

        $swipes = $query->paginate(20);

        return $this->sendResponse($swipes, 'Swipe history retrieved.');
    }

    /**
     * View list of users who liked / starred this user (See who likes you).
     */
    public function getLikesReceived(Request $request): JsonResponse
    {
        $user = $request->user();
        $isPremium = $user->hasActivePremium();

        $likes = Swipe::with(['user.photos', 'user.profile', 'user.location'])
            ->where('target_user_id', $user->id)
            ->whereIn('type', ['like', 'star'])
            ->latest('id')
            ->paginate(20);

        $transformed = $likes->getCollection()->map(function ($swipe) use ($isPremium) {
            $sender = $swipe->user;
            $avatar = $sender?->photos->where('is_primary', true)->first()?->url ?? $sender?->photos->first()?->url;

            // If free user, hide sensitive profile details & blur names
            if (!$isPremium) {
                return [
                    'id' => $swipe->id,
                    'type' => $swipe->type,
                    'created_at' => $swipe->created_at->toIso8601String(),
                    'is_blurred' => true,
                    'sender' => [
                        'id' => null,
                        'name' => substr($sender?->name ?? 'User', 0, 1) . '***',
                        'age' => $sender?->age,
                        'avatar' => $avatar,
                        'city' => $sender?->location?->city,
                    ],
                ];
            }

            return [
                'id' => $swipe->id,
                'type' => $swipe->type,
                'created_at' => $swipe->created_at->toIso8601String(),
                'is_blurred' => false,
                'sender' => [
                    'id' => $sender?->id,
                    'name' => $sender?->name,
                    'age' => $sender?->age,
                    'avatar' => $avatar,
                    'occupation' => $sender?->profile?->occupation,
                    'city' => $sender?->location?->city,
                ],
            ];
        });

        $likes->setCollection($transformed);

        return $this->sendResponse([
            'is_premium' => $isPremium,
            'total_likes' => $likes->total(),
            'likes' => $likes,
        ], 'Likes received retrieved.');
    }
}
