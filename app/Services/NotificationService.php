<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\UserInfo;
use App\Models\Post;

class NotificationService
{
    protected ExpSystemService $expService;

    public function __construct(ExpSystemService $expService)
    {
        $this->expService = $expService;
    }

    /**
     * Create a notification when someone likes a post.
     */
    public function notifyPostLike(int $postOwnerId, int $likerId, int $postId): ?Notification
    {
        // Don't notify if user liked their own post
        if ($postOwnerId === $likerId) {
            return null;
        }

        $liker = User::find($likerId);
        $likerInfo = $liker ? UserInfo::findByUserId($likerId) : null;
        $likerName = $likerInfo ? $likerInfo->username : 'Someone';

        $post = Post::find($postId);
        $postTitle = $post ? substr($post->title, 0, 30) : 'your post';

        if (strlen($post->title ?? '') > 30) {
            $postTitle .= '...';
        }

        $message = "{$likerName} liked your post \"{$postTitle}\"";

        // Add EXP for the post owner
        $this->expService->addExp($postOwnerId, ExpSystemService::EXP_PER_LIKE);

        return Notification::createLikeNotification($postOwnerId, $likerId, $postId, $message);
    }

    /**
     * Create a notification when someone comments on a post.
     */
    public function notifyPostComment(int $postOwnerId, int $commenterId, int $postId): ?Notification
    {
        // Don't notify if user commented on their own post
        if ($postOwnerId === $commenterId) {
            return null;
        }

        $commenter = User::find($commenterId);
        $commenterInfo = $commenter ? UserInfo::findByUserId($commenterId) : null;
        $commenterName = $commenterInfo ? $commenterInfo->username : 'Someone';

        $post = Post::find($postId);
        $postTitle = $post ? substr($post->title, 0, 30) : 'your post';

        if (strlen($post->title ?? '') > 30) {
            $postTitle .= '...';
        }

        $message = "{$commenterName} commented on your post \"{$postTitle}\"";

        return Notification::createCommentNotification($postOwnerId, $commenterId, $postId, $message);
    }

    /**
     * Create a notification when someone replies to a comment.
     */
    public function notifyCommentReply(int $commentOwnerId, int $replierId, int $postId): ?Notification
    {
        // Don't notify if user replied to their own comment
        if ($commentOwnerId === $replierId) {
            return null;
        }

        $replier = User::find($replierId);
        $replierInfo = $replier ? UserInfo::findByUserId($replierId) : null;
        $replierName = $replierInfo ? $replierInfo->username : 'Someone';

        $message = "{$replierName} replied to your comment";

        return Notification::createCommentNotification($commentOwnerId, $replierId, $postId, $message);
    }

    /**
     * Create a notification when someone likes a comment.
     */
    public function notifyCommentLike(int $commentOwnerId, int $likerId, int $postId): ?Notification
    {
        // Don't notify if user liked their own comment
        if ($commentOwnerId === $likerId) {
            return null;
        }

        $liker = User::find($likerId);
        $likerInfo = $liker ? UserInfo::findByUserId($likerId) : null;
        $likerName = $likerInfo ? $likerInfo->username : 'Someone';

        $message = "{$likerName} liked your comment";

        // Add EXP for the comment owner
        $this->expService->addExp($commentOwnerId, ExpSystemService::EXP_PER_LIKE);

        return Notification::createLikeNotification($commentOwnerId, $likerId, $postId, $message);
    }

    /**
     * Get notifications for a user.
     */
    public function getNotifications(int $userId, bool $unreadOnly = false): array
    {
        return Notification::forUser($userId, $unreadOnly);
    }

    /**
     * Get unread notification count for a user.
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::countUnread($userId);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(int $notificationId): bool
    {
        $notification = Notification::find($notificationId);

        if (!$notification) {
            return false;
        }

        return $notification->markAsRead();
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(int $userId): int
    {
        return Notification::markAllAsRead($userId);
    }

    /**
     * Handle unliking a post (remove EXP).
     */
    public function handlePostUnlike(int $postOwnerId, int $unlikerId): void
    {
        // Don't remove EXP if user unliked their own post
        if ($postOwnerId === $unlikerId) {
            return;
        }

        $this->expService->removeExp($postOwnerId, ExpSystemService::EXP_PER_LIKE);
    }

    /**
     * Handle unliking a comment (remove EXP).
     */
    public function handleCommentUnlike(int $commentOwnerId, int $unlikerId): void
    {
        // Don't remove EXP if user unliked their own comment
        if ($commentOwnerId === $unlikerId) {
            return;
        }

        $this->expService->removeExp($commentOwnerId, ExpSystemService::EXP_PER_LIKE);
    }
}
