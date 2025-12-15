<?php

namespace App\Models;

class BanReview extends SupabaseModel
{
    protected static string $table = 'ban_reviews';

    // Status types
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Create a ban review request.
     */
    public static function createRequest(string $username, int $postId, string $flaggedBy, string $reason): static
    {
        return static::create([
            'username' => $username,
            'post_id' => $postId,
            'flagged_by' => $flaggedBy,
            'reason' => $reason,
            'status' => self::STATUS_PENDING
        ]);
    }

    /**
     * Get pending ban reviews.
     */
    public static function pending(): array
    {
        return static::hydrate(
            static::query()
                ->where('status', self::STATUS_PENDING)
                ->orderByDesc('created_at')
                ->get()
        );
    }

    /**
     * Get all ban reviews with optional status filter.
     */
    public static function getAll(?string $status = null): array
    {
        $query = static::query()->orderByDesc('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        return static::hydrate($query->get());
    }

    /**
     * Get ban reviews for a specific user.
     */
    public static function forUser(string $username): array
    {
        return static::hydrate(
            static::query()
                ->where('username', $username)
                ->orderByDesc('created_at')
                ->get()
        );
    }

    /**
     * Approve the ban review.
     */
    public function approve(string $reviewedBy): bool
    {
        $this->status = self::STATUS_APPROVED;
        $this->reviewed_by = $reviewedBy;
        $this->reviewed_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * Reject the ban review.
     */
    public function reject(string $reviewedBy): bool
    {
        $this->status = self::STATUS_REJECTED;
        $this->reviewed_by = $reviewedBy;
        $this->reviewed_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * Check if the review is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the review is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if the review is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Get the related post.
     */
    public function getPost(): ?Post
    {
        return Post::find($this->post_id);
    }
}
