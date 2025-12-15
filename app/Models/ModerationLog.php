<?php

namespace App\Models;

class ModerationLog extends SupabaseModel
{
    protected static string $table = 'moderation_log';

    // Action types
    public const ACTION_HIDE = 'hide';
    public const ACTION_UNHIDE = 'unhide';
    public const ACTION_FLAG = 'flag';

    /**
     * Log a moderation action.
     */
    public static function log(int $postId, string $moderator, string $action, ?string $reason = null): static
    {
        return static::create([
            'post_id' => $postId,
            'moderator' => $moderator,
            'action' => $action,
            'reason' => $reason
        ]);
    }

    /**
     * Log hiding a post.
     */
    public static function logHide(int $postId, string $moderator, ?string $reason = null): static
    {
        return static::log($postId, $moderator, self::ACTION_HIDE, $reason);
    }

    /**
     * Log unhiding a post.
     */
    public static function logUnhide(int $postId, string $moderator, ?string $reason = null): static
    {
        return static::log($postId, $moderator, self::ACTION_UNHIDE, $reason);
    }

    /**
     * Log flagging a post.
     */
    public static function logFlag(int $postId, string $moderator, ?string $reason = null): static
    {
        return static::log($postId, $moderator, self::ACTION_FLAG, $reason);
    }

    /**
     * Get moderation history for a post.
     */
    public static function forPost(int $postId): array
    {
        return static::hydrate(
            static::query()
                ->where('post_id', $postId)
                ->orderByDesc('created_at')
                ->get()
        );
    }

    /**
     * Get moderation actions by a moderator.
     */
    public static function byModerator(string $moderator): array
    {
        return static::hydrate(
            static::query()
                ->where('moderator', $moderator)
                ->orderByDesc('created_at')
                ->get()
        );
    }

    /**
     * Get recent moderation actions.
     */
    public static function recent(int $limit = 50): array
    {
        return static::hydrate(
            static::query()
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get()
        );
    }
}
