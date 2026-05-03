<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn ($notification) => $this->serializeNotification($notification));

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request, string $notification)
    {
        $record = $request->user()
            ->notifications()
            ->where('id', $notification)
            ->firstOrFail();

        $record->markAsRead();

        return response()->json([
            'notification' => $this->serializeNotification($record->fresh()),
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'message' => __('messages.notifications_read'),
            'unread_count' => 0,
        ]);
    }

    private function serializeNotification($notification): array
    {
        $data = $notification->data ?? [];
        $postId = $data['post_id'] ?? null;

        return [
            'id' => $notification->id,
            'type' => class_basename($notification->type),
            'message' => strip_tags($data['message'] ?? 'Notification'),
            'post_id' => $postId,
            'comment_id' => $data['comment_id'] ?? null,
            'reply_id' => $data['reply_id'] ?? null,
            'kind' => $data['type'] ?? $this->inferKind($notification->type),
            'url' => $postId ? "/posts/{$postId}" : null,
            'read_at' => $notification->read_at,
            'created_at' => $notification->created_at,
        ];
    }

    private function inferKind(string $type): string
    {
        return match (class_basename($type)) {
            'PostLikedNotification' => 'post_like',
            'PostCommented' => 'comment',
            'CommentLiked' => 'comment_like',
            'CommentReplied' => 'reply',
            'MentionedInComment', 'MentionedInReply' => 'mention',
            default => 'notification',
        };
    }
}
