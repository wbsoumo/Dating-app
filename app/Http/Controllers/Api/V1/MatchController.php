<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\UserMatch;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MatchController extends BaseApiController
{
    /**
     * List all active matches for the authenticated user.
     */
    public function listMatches(Request $request): JsonResponse
    {
        $user = $request->user();

        $matches = UserMatch::with([
            'user1.photos', 'user1.profile', 'user1.location',
            'user2.photos', 'user2.profile', 'user2.location',
        ])
        ->where('status', 'active')
        ->where(function ($q) use ($user) {
            $q->where('user1_id', $user->id)
              ->orWhere('user2_id', $user->id);
        })
        ->orderBy('last_message_at', 'desc')
        ->orderBy('matched_at', 'desc')
        ->paginate(20);

        $transformed = $matches->getCollection()->map(function (UserMatch $match) use ($user) {
            $partner = $match->getPartner($user->id);
            $avatar = $partner?->photos->where('is_primary', true)->first()?->url ?? $partner?->photos->first()?->url;

            $latestMsg = \App\Models\Message::where('match_id', $match->id)->latest()->first();
            $unreadCount = \App\Models\Message::where('match_id', $match->id)
                ->where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();

            return [
                'match_id' => $match->id,
                'matched_at' => $match->matched_at->toIso8601String(),
                'last_message_at' => $match->last_message_at?->toIso8601String(),
                'unread_count' => $unreadCount,
                'last_message' => $latestMsg ? [
                    'id' => $latestMsg->id,
                    'message' => $latestMsg->message,
                    'type' => $latestMsg->type,
                    'media_url' => $latestMsg->media_url,
                    'is_me' => ($latestMsg->sender_id === $user->id),
                    'created_at' => $latestMsg->created_at->toIso8601String(),
                ] : null,
                'partner' => [
                    'id' => $partner?->id,
                    'name' => $partner?->name,
                    'age' => $partner?->age,
                    'gender' => $partner?->gender,
                    'is_verified' => (bool) $partner?->is_verified,
                    'is_premium' => (bool) $partner?->is_premium,
                    'avatar' => $avatar,
                    'occupation' => $partner?->profile?->occupation,
                    'city' => $partner?->location?->city,
                ],
            ];
        });

        $matches->setCollection($transformed);

        return $this->sendResponse($matches, 'Matches retrieved.');
    }

    /**
     * Get single match detail.
     */
    public function getMatch(Request $request, int $matchId): JsonResponse
    {
        $user = $request->user();

        $match = UserMatch::with([
            'user1.photos', 'user1.profile', 'user1.location', 'user1.interests',
            'user2.photos', 'user2.profile', 'user2.location', 'user2.interests',
        ])
        ->where('id', $matchId)
        ->where(function ($q) use ($user) {
            $q->where('user1_id', $user->id)
              ->orWhere('user2_id', $user->id);
        })
        ->first();

        if (!$match) {
            return $this->sendError('Match not found.', null, 404);
        }

        $partner = $match->getPartner($user->id);

        return $this->sendResponse([
            'match_id' => $match->id,
            'status' => $match->status,
            'matched_at' => $match->matched_at->toIso8601String(),
            'partner' => [
                'id' => $partner?->id,
                'name' => $partner?->name,
                'age' => $partner?->age,
                'gender' => $partner?->gender,
                'is_verified' => (bool) $partner?->is_verified,
                'is_premium' => (bool) $partner?->is_premium,
                'bio' => $partner?->profile?->bio,
                'occupation' => $partner?->profile?->occupation,
                'education' => $partner?->profile?->education,
                'religion' => $partner?->profile?->religion,
                'relationship_goal' => $partner?->profile?->relationship_goal,
                'city' => $partner?->location?->city,
                'photos' => $partner?->photos->map(fn ($p) => ['id' => $p->id, 'url' => $p->url, 'is_primary' => (bool) $p->is_primary]),
                'interests' => $partner?->interests->map(fn ($i) => ['id' => $i->id, 'name' => $i->name, 'icon' => $i->icon]),
            ],
        ], 'Match details retrieved.');
    }

    /**
     * Unmatch a user.
     */
    public function unmatch(Request $request, int $matchId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $user = $request->user();

        $match = UserMatch::where('id', $matchId)
            ->where('status', 'active')
            ->where(function ($q) use ($user) {
                $q->where('user1_id', $user->id)
                  ->orWhere('user2_id', $user->id);
            })
            ->first();

        if (!$match) {
            return $this->sendError('Active match not found.', null, 404);
        }

        $match->update([
            'status' => 'unmatched',
            'unmatched_by' => $user->id,
            'unmatch_reason' => $request->reason ?? 'User decided to unmatch',
        ]);

        return $this->sendResponse(null, 'Successfully unmatched.');
    }
}
