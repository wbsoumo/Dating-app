<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Notification;
use App\Models\Setting;
use App\Models\StarPackage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StarController extends BaseApiController
{
    /**
     * List all available star packages for purchase.
     */
    public function listPackages(): JsonResponse
    {
        $packages = StarPackage::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        return $this->sendResponse($packages, 'Star packages retrieved.');
    }

    /**
     * Get user's star wallet balance and transaction history.
     */
    public function getWallet(Request $request): JsonResponse
    {
        $user = $request->user();
        $wallet = $user->getOrCreateWallet();

        $transactions = $wallet->transactions()->paginate(20);

        return $this->sendResponse([
            'balance' => $wallet->balance,
            'transactions' => $transactions,
        ], 'Star wallet retrieved.');
    }

    /**
     * Send stars / Super Like directly to another user.
     */
    public function sendStar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'target_user_id' => 'required|exists:users,id',
            'stars' => 'nullable|integer|min:1|max:100',
            'message' => 'nullable|string|max:200',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $user = $request->user();
        $targetUserId = (int) $request->target_user_id;

        if ($user->id === $targetUserId) {
            return $this->sendError('You cannot send stars to yourself.', null, 400);
        }

        $starsToSend = (int) $request->input('stars', (int) Setting::get('super_like_star_cost', 1));
        $wallet = $user->getOrCreateWallet();

        if ($wallet->balance < $starsToSend) {
            return $this->sendError('Insufficient star balance.', ['balance' => $wallet->balance], 400);
        }

        $targetUser = User::findOrFail($targetUserId);

        return DB::transaction(function () use ($user, $targetUser, $starsToSend, $wallet, $request) {
            $wallet->debit(
                $starsToSend,
                "Sent {$starsToSend} Stars to {$targetUser->name}",
                'swipe_spent',
                (string) $targetUser->id
            );

            // Also credit target user's wallet with stars as a gift!
            $targetWallet = $targetUser->getOrCreateWallet();
            $targetWallet->credit(
                $starsToSend,
                "Received {$starsToSend} Stars from {$user->name}",
                'gift_received',
                (string) $user->id
            );

            Notification::create([
                'user_id' => $targetUser->id,
                'title' => 'You Received Stars! ⭐',
                'body' => "{$user->name} sent you {$starsToSend} Stars!" . ($request->filled('message') ? " \"{$request->message}\"" : ""),
                'type' => 'star',
                'data' => [
                    'sender_id' => $user->id,
                    'stars' => $starsToSend,
                    'message' => $request->message,
                ],
            ]);

            return $this->sendResponse([
                'stars_sent' => $starsToSend,
                'new_balance' => $wallet->balance,
            ], "Successfully sent {$starsToSend} Stars to {$targetUser->name}!");
        });
    }
}
