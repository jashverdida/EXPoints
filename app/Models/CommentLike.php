<?php

namespace App\Models;

class CommentLike extends SupabaseModel
{
    protected static string $table = 'comment_likes';

    /**
     * Get the comment.
     */
    public function getComment(): ?PostComment
    {
        return PostComment::find($this->comment_id);
    }

    /**
     * Get the user who liked.
     */
    public function getUser(): ?User
    {
        return User::find($this->user_id);
    }

    /**
     * Check if a like exists.
     */
    public static function exists(int $commentId, int $userId): bool
    {
        return static::query()
            ->where('comment_id', $commentId)
            ->where('user_id', $userId)
            ->exists();
    }
}
