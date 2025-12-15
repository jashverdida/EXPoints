<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $unreadOnly = $request->boolean('unread_only', false);

        $notifications = $this->notificationService->getNotifications($user->id, $unreadOnly);

        return response()->json(array_map(function ($notification) {
            return $notification->toArray();
        }, $notifications));
    }

    /**
     * Get unread notification count.
     */
    public function count(): JsonResponse
    {
        $user = Auth::user();
        $count = $this->notificationService->getUnreadCount($user->id);

        return response()->json(['count' => $count]);
    }

    /**
     * Mark a notification as read.
     */
    public function markRead(int $notificationId): JsonResponse
    {
        $notification = Notification::find($notificationId);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $user = Auth::user();

        // Check ownership
        if ($notification->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $success = $this->notificationService->markAsRead($notificationId);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Notification marked as read' : 'Failed to mark notification as read'
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(): JsonResponse
    {
        $user = Auth::user();
        $count = $this->notificationService->markAllAsRead($user->id);

        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => "{$count} notification(s) marked as read"
        ]);
    }
}
