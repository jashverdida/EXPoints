<?php

namespace App\Models;

class Notification extends SupabaseModel
{
    protected static string $table = 'notifications';

    // Notification types
    public const TYPE_LIKE = 'like';
    public const TYPE_COMMENT = 'comment';
    public const TYPE_LEVEL_UP = 'level_up';

    /**
     * Get notifications for a user.
     */
    public static function forUser(int $userId, bool $unreadOnly = false): array
    {
        $query = static::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at');

        if ($unreadOnly) {
            $query->where('is_read', 0);
        }

        return static::hydrate($query->get());
    }

    /**
     * Get unread notifications for a user.
     */
    public static function unread(int $userId): array
    {
        return static::forUser($userId, true);
    }

    /**
     * Count unread notifications for a user.
     */
    public static function countUnread(int $userId): int
    {
        return static::query()
            ->where('user_id', $userId)
            ->where('is_read', 0)
            ->count();
    }

    /**
     * Create a like notification.
     */
    public static function createLikeNotification(int $userId, int $fromUserId, int $postId, string $message): static
    {
        return static::create([
            'user_id' => $userId,
            'from_user_id' => $fromUserId,
            'post_id' => $postId,
            'type' => self::TYPE_LIKE,
            'message' => $message,
            'is_read' => 0
        ]);
    }

    /**
     * Create a comment notification.
     */
    public static function createCommentNotification(int $userId, int $fromUserId, int $postId, string $message): static
    {
        return static::create([
            'user_id' => $userId,
            'from_user_id' => $fromUserId,
            'post_id' => $postId,
            'type' => self::TYPE_COMMENT,
            'message' => $message,
            'is_read' => 0
        ]);
    }

    /**
     * Create a level up notification.
     */
    public static function createLevelUpNotification(int $userId, int $level, string $message): static
    {
        return static::create([
            'user_id' => $userId,
            'type' => self::TYPE_LEVEL_UP,
            'message' => $message,
            'is_read' => 0
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(): bool
    {
        $this->is_read = 1;
        return $this->save();
    }

    /**
     * Mark all notifications as read for a user.
     */
    public static function markAllAsRead(int $userId): int
    {
        $notifications = static::forUser($userId, true);
        $count = 0;

        foreach ($notifications as $notification) {
            if ($notification->markAsRead()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Check if notification is read.
     */
    public function isRead(): bool
    {
        return (bool) $this->is_read;
    }

    /**
     * Get the related post.
     */
    public function getPost(): ?Post
    {
        if (!$this->post_id) {
            return null;
        }

        return Post::find($this->post_id);
    }

    /**
     * Get the user who triggered the notification.
     */
    public function getFromUser(): ?User
    {
        if (!$this->from_user_id) {
            return null;
        }

        return User::find($this->from_user_id);
    }
}
