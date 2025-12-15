<?php

namespace App\Models;

class PostComment extends SupabaseModel
{
    protected static string $table = 'post_comments';

    /**
     * Get comments for a post.
     */
    public static function forPost(int $postId, bool $topLevelOnly = true): array
    {
        $query = static::query()
            ->where('post_id', $postId)
            ->orderByDesc('created_at');

        if ($topLevelOnly) {
            $query->whereNull('parent_comment_id');
        }

        return static::hydrate($query->get());
    }

    /**
     * Get replies to a comment.
     */
    public static function replies(int $commentId): array
    {
        $results = static::query()
            ->where('parent_comment_id', $commentId)
            ->orderBy('created_at', 'asc')
            ->get();

        return static::hydrate($results);
    }

    /**
     * Get the post this comment belongs to.
     */
    public function getPost(): ?Post
    {
        return Post::find($this->post_id);
    }

    /**
     * Get the comment author.
     */
    public function getAuthor(): ?User
    {
        return User::find($this->user_id);
    }

    /**
     * Get the parent comment (if this is a reply).
     */
    public function getParent(): ?static
    {
        if (!$this->parent_comment_id) {
            return null;
        }

        return static::find($this->parent_comment_id);
    }

    /**
     * Get replies to this comment.
     */
    public function getReplies(): array
    {
        return static::replies($this->id);
    }

    /**
     * Get reply count.
     */
    public function getReplyCount(): int
    {
        return static::query()
            ->where('parent_comment_id', $this->id)
            ->count();
    }

    /**
     * Check if this comment has replies.
     */
    public function hasReplies(): bool
    {
        return $this->getReplyCount() > 0;
    }

    /**
     * Check if user has liked this comment.
     */
    public function isLikedBy(int $userId): bool
    {
        return CommentLike::query()
            ->where('comment_id', $this->id)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Toggle like for a user.
     */
    public function toggleLike(int $userId): bool
    {
        $existing = CommentLike::query()
            ->where('comment_id', $this->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            static::getService()->delete('comment_likes', [
                'comment_id' => $this->id,
                'user_id' => $userId
            ]);
            $this->like_count = max(0, ($this->like_count ?? 0) - 1);
            $this->save();
            return false; // Unliked
        }

        CommentLike::create([
            'comment_id' => $this->id,
            'user_id' => $userId
        ]);
        $this->like_count = ($this->like_count ?? 0) + 1;
        $this->save();
        return true; // Liked
    }

    /**
     * Add a reply to this comment.
     */
    public function addReply(int $userId, string $username, string $content): static
    {
        $reply = static::create([
            'post_id' => $this->post_id,
            'parent_comment_id' => $this->id,
            'user_id' => $userId,
            'username' => $username,
            'comment' => $content,
            'like_count' => 0,
            'reply_count' => 0
        ]);

        // Update reply count on parent
        $this->reply_count = ($this->reply_count ?? 0) + 1;
        $this->save();

        return $reply;
    }
}
