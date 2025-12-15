<?php

namespace App\Models;

class PostBookmark extends SupabaseModel
{
    protected static string $table = 'post_bookmarks';

    /**
     * Get the post.
     */
    public function getPost(): ?Post
    {
        return Post::find($this->post_id);
    }

    /**
     * Get the user who bookmarked.
     */
    public function getUser(): ?User
    {
        return User::find($this->user_id);
    }

    /**
     * Check if a bookmark exists.
     */
    public static function exists(int $postId, int $userId): bool
    {
        return static::query()
            ->where('post_id', $postId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Get bookmarks for a user.
     */
    public static function byUser(int $userId): array
    {
        return static::hydrate(
            static::query()
                ->where('user_id', $userId)
                ->orderByDesc('created_at')
                ->get()
        );
    }

    /**
     * Get bookmarked posts for a user.
     */
    public static function getBookmarkedPosts(int $userId): array
    {
        $bookmarks = static::byUser($userId);
        $posts = [];

        foreach ($bookmarks as $bookmark) {
            $post = $bookmark->getPost();
            if ($post && !$post->hidden) {
                $posts[] = $post;
            }
        }

        return $posts;
    }
}
