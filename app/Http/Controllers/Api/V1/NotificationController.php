<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends BaseApiController
{
    /**
     * List user notifications.
     */
    public function listNotifications(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = Notification::where('user_id', $user->id)
            ->latest('id')
            ->paginate(20);

        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return $this->sendResponse([
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ], 'Notifications retrieved.');
    }

    /**
     * Mark single notification as read.
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $notif = Notification::where('id', $id)->where('user_id', $user->id)->first();

        if (!$notif) {
            return $this->sendError('Notification not found.', null, 404);
        }

        $notif->update(['is_read' => true]);

        return $this->sendResponse($notif, 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return $this->sendResponse(null, 'All notifications marked as read.');
    }
}
