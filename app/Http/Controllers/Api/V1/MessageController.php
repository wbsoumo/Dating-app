<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Message;
use App\Models\Notification;
use App\Models\UserMatch;
use App\Services\FileService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends BaseApiController
{
    protected FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Retrieve paginated chat messages for a match.
     */
    public function getMessages(Request $request, int $matchId): JsonResponse
    {
        $user = $request->user();

        $match = UserMatch::where('id', $matchId)
            ->where(function ($q) use ($user) {
                $q->where('user1_id', $user->id)
                  ->orWhere('user2_id', $user->id);
            })
            ->first();

        if (!$match) {
            return $this->sendError('Match conversation not found.', null, 404);
        }

        // Auto mark partner's unread messages as read
        Message::where('match_id', $matchId)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => Carbon::now(),
            ]);

        $partner = $match->getPartner($user->id);

        $perPage = (int) $request->get('per_page', 40);
        $messages = Message::where('match_id', $matchId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $transformed = $messages->getCollection()->map(function (Message $m) use ($user) {
            return [
                'id' => $m->id,
                'match_id' => $m->match_id,
                'sender_id' => $m->sender_id,
                'receiver_id' => $m->receiver_id,
                'is_me' => ($m->sender_id === $user->id),
                'message' => $m->message,
                'type' => $m->type,
                'media_url' => $m->media_url,
                'is_read' => (bool) $m->is_read,
                'read_at' => $m->read_at?->toIso8601String(),
                'created_at' => $m->created_at->toIso8601String(),
            ];
        });

        $messages->setCollection($transformed);

        return $this->sendResponse([
            'match' => [
                'id' => $match->id,
                'status' => $match->status,
                'partner' => [
                    'id' => $partner?->id,
                    'name' => $partner?->name,
                    'avatar' => $partner?->photos->where('is_primary', true)->first()?->url ?? $partner?->photos->first()?->url,
                    'is_verified' => (bool) $partner?->is_verified,
                    'is_premium' => (bool) $partner?->is_premium,
                    'last_active_at' => $partner?->last_active_at?->toIso8601String(),
                ],
            ],
            'messages' => $messages,
        ], 'Chat messages retrieved.');
    }

    /**
     * Send a message to a matched partner.
     */
    public function sendMessage(Request $request, int $matchId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => 'nullable|string|max:2000',
            'type' => 'nullable|in:text,image,star_gift',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
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

        $partner = $match->getPartner($user->id);
        if (!$partner) {
            return $this->sendError('Partner not found.', null, 404);
        }

        $type = $request->get('type', 'text');
        $mediaUrl = null;
        $text = $request->get('message');

        if ($request->hasFile('image')) {
            $type = 'image';
            try {
                $mediaUrl = $this->fileService->uploadChatMedia($user, $request->file('image'));
            } catch (\Throwable $e) {
                return $this->sendError('Image upload failed: ' . $e->getMessage(), null, 400);
            }
        }

        if (empty($text) && empty($mediaUrl)) {
            return $this->sendError('Message text or image is required.', null, 422);
        }

        $now = Carbon::now();

        $msg = Message::create([
            'match_id' => $match->id,
            'sender_id' => $user->id,
            'receiver_id' => $partner->id,
            'message' => $text,
            'type' => $type,
            'media_url' => $mediaUrl,
            'is_read' => false,
            'read_at' => null,
        ]);

        // Update match last_message_at
        $match->update(['last_message_at' => $now]);

        // Create in-app notification for receiver
        try {
            Notification::create([
                'user_id' => $partner->id,
                'type' => 'new_message',
                'title' => 'New message from ' . $user->name,
                'body' => $type === 'image' ? 'Sent a photo' : ($text ?? 'Sent a message'),
                'data' => [
                    'match_id' => $match->id,
                    'sender_id' => $user->id,
                    'message_id' => $msg->id,
                ],
                'is_read' => false,
            ]);
        } catch (\Throwable $e) {
            // Silently log or continue
        }

        return $this->sendResponse([
            'id' => $msg->id,
            'match_id' => $msg->match_id,
            'sender_id' => $msg->sender_id,
            'receiver_id' => $msg->receiver_id,
            'is_me' => true,
            'message' => $msg->message,
            'type' => $msg->type,
            'media_url' => $msg->media_url,
            'is_read' => false,
            'read_at' => null,
            'created_at' => $msg->created_at->toIso8601String(),
        ], 'Message sent successfully.', 201);
    }

    /**
     * Mark all messages in a match as read.
     */
    public function markAsRead(Request $request, int $matchId): JsonResponse
    {
        $user = $request->user();

        $updated = Message::where('match_id', $matchId)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => Carbon::now(),
            ]);

        return $this->sendResponse(['marked_count' => $updated], 'Messages marked as read.');
    }

    /**
     * Delete a single message (only if current user is the sender).
     */
    public function deleteMessage(Request $request, int $messageId): JsonResponse
    {
        $user = $request->user();

        $message = Message::where('id', $messageId)
            ->where('sender_id', $user->id)
            ->first();

        if (!$message) {
            return $this->sendError('Message not found or unauthorized to delete.', null, 404);
        }

        $message->delete();

        return $this->sendResponse(null, 'Message deleted successfully.');
    }
}
