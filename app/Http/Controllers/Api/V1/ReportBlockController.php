<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportBlockController extends BaseApiController
{
    /**
     * Report another user for harassment, fake profile, inappropriate content, etc.
     */
    public function reportUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reported_user_id' => 'required|exists:users,id',
            'reason' => 'required|string|max:100',
            'details' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $user = $request->user();
        $reportedUserId = (int) $request->reported_user_id;

        if ($user->id === $reportedUserId) {
            return $this->sendError('You cannot report yourself.', null, 400);
        }

        $report = UserReport::create([
            'reporter_id' => $user->id,
            'reported_user_id' => $reportedUserId,
            'reason' => $request->reason,
            'details' => $request->details,
            'status' => 'pending',
        ]);

        return $this->sendResponse($report, 'Report submitted successfully. Our safety team will review it.');
    }

    /**
     * Block a user.
     */
    public function blockUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'blocked_user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $user = $request->user();
        $blockedUserId = (int) $request->blocked_user_id;

        if ($user->id === $blockedUserId) {
            return $this->sendError('You cannot block yourself.', null, 400);
        }

        $block = UserBlock::firstOrCreate([
            'user_id' => $user->id,
            'blocked_user_id' => $blockedUserId,
        ]);

        return $this->sendResponse($block, 'User blocked successfully.');
    }

    /**
     * List users blocked by authenticated user.
     */
    public function listBlocked(Request $request): JsonResponse
    {
        $user = $request->user();

        $blocked = UserBlock::with('blockedUser')
            ->where('user_id', $user->id)
            ->latest('id')
            ->paginate(20);

        return $this->sendResponse($blocked, 'Blocked users retrieved.');
    }

    /**
     * Unblock a user.
     */
    public function unblockUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'blocked_user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $user = $request->user();
        UserBlock::where('user_id', $user->id)
            ->where('blocked_user_id', $request->blocked_user_id)
            ->delete();

        return $this->sendResponse(null, 'User unblocked successfully.');
    }
}
