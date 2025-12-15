<?php

namespace App\Models;

class Post extends SupabaseModel
{
    protected static string $table = 'posts';

    /**
     * Get posts with pagination.
     */
    public static function paginated(int $page = 1, int $perPage = 15, array $filters = []): array
    {
        $query = static::query()
            ->select('*')
            ->where('hidden', 0)
            ->orderByDesc('created_at')
            ->paginate($page, $perPage);

        foreach ($filters as $column => $value) {
            $query->where($column, $value);
        }

        return static::hydrate($query->get());
    }

    /**
     * Get popular posts (sorted by likes).
     */
    public static function popular(int $limit = 10): array
    {
        $results = static::query()
            ->where('hidden', 0)
            ->orderByDesc('likes')
            ->limit($limit)
            ->get();

        return static::hydrate($results);
    }

    /**
     * Get newest posts.
     */
    public static function newest(int $limit = 10): array
    {
        $results = static::query()
            ->where('hidden', 0)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return static::hydrate($results);
    }

    /**
     * Get posts by game.
     */
    public static function byGame(string $game, int $limit = 20): array
    {
        $results = static::query()
            ->where('game', $game)
            ->where('hidden', 0)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return static::hydrate($results);
    }

    /**
     * Get posts by user.
     */
    public static function byUser(int $userId): array
    {
        $results = static::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();

        return static::hydrate($results);
    }

    /**
     * Search posts by title or content.
     */
    public static function search(string $term, int $limit = 20): array
    {
        $results = static::query()
            ->whereILike('title', '%' . $term . '%')
            ->where('hidden', 0)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return static::hydrate($results);
    }

    /**
     * Get the post author.
     */
    public function getAuthor(): ?User
    {
        return User::find($this->user_id);
    }

    /**
     * Get post comments.
     */
    public function getComments(): array
    {
        return PostComment::where('post_id', $this->id)
            ->whereNull('parent_comment_id')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get comment count.
     */
    public function getCommentCount(): int
    {
        return PostComment::query()
            ->where('post_id', $this->id)
            ->count();
    }

    /**
     * Get like count.
     */
    public function getLikeCount(): int
    {
        return PostLike::query()
            ->where('post_id', $this->id)
            ->count();
    }

    /**
     * Check if user has liked this post.
     */
    public function isLikedBy(int $userId): bool
    {
        return PostLike::query()
            ->where('post_id', $this->id)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Check if user has bookmarked this post.
     */
    public function isBookmarkedBy(int $userId): bool
    {
        return PostBookmark::query()
            ->where('post_id', $this->id)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Toggle like for a user.
     */
    public function toggleLike(int $userId): bool
    {
        $existing = PostLike::query()
            ->where('post_id', $this->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            static::getService()->delete('post_likes', [
                'post_id' => $this->id,
                'user_id' => $userId
            ]);
            $this->likes = max(0, ($this->likes ?? 0) - 1);
            $this->save();
            return false; // Unliked
        }

        PostLike::create([
            'post_id' => $this->id,
            'user_id' => $userId
        ]);
        $this->likes = ($this->likes ?? 0) + 1;
        $this->save();
        return true; // Liked
    }

    /**
     * Toggle bookmark for a user.
     */
    public function toggleBookmark(int $userId): bool
    {
        $existing = PostBookmark::query()
            ->where('post_id', $this->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            static::getService()->delete('post_bookmarks', [
                'post_id' => $this->id,
                'user_id' => $userId
            ]);
            return false; // Unbookmarked
        }

        PostBookmark::create([
            'post_id' => $this->id,
            'user_id' => $userId
        ]);
        return true; // Bookmarked
    }

    /**
     * Hide the post (moderation).
     */
    public function hide(): bool
    {
        $this->hidden = 1;
        return $this->save();
    }

    /**
     * Unhide the post (moderation).
     */
    public function unhide(): bool
    {
        $this->hidden = 0;
        return $this->save();
    }
}
