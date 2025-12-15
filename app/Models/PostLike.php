<?php

namespace App\Models;

class PostLike extends SupabaseModel
{
    protected static string $table = 'post_likes';

    /**
     * Get the post.
     */
    public function getPost(): ?Post
    {
        return Post::find($this->post_id);
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
    public static function exists(int $postId, int $userId): bool
    {
        return static::query()
            ->where('post_id', $postId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Get likes for a post.
     */
    public static function forPost(int $postId): array
    {
        return static::hydrate(
            static::query()
                ->where('post_id', $postId)
                ->get()
        );
    }

    /**
     * Get likes by a user.
     */
    public static function byUser(int $userId): array
    {
        return static::hydrate(
            static::query()
                ->where('user_id', $userId)
                ->get()
        );
    }
}
