<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'username',
        'game',
        'title',
        'content',
        'hidden',
    ];

    protected $casts = [
        'hidden' => 'boolean',
    ];

    /**
     * Get the user that owns the post.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user info for this post's author.
     */
    public function authorInfo()
    {
        return $this->hasOneThrough(
            UserInfo::class,
            User::class,
            'id',           // Foreign key on users table
            'user_id',      // Foreign key on user_info table
            'user_id',      // Local key on posts table
            'id'            // Local key on users table
        );
    }

    /**
     * Get all comments for this post.
     */
    public function comments()
    {
        return $this->hasMany(PostComment::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get all likes for this post.
     */
    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    /**
     * Get all bookmarks for this post.
     */
    public function bookmarks()
    {
        return $this->hasMany(PostBookmark::class);
    }

    /**
     * Get users who liked this post.
     */
    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'post_likes')->withTimestamps();
    }

    /**
     * Get users who bookmarked this post.
     */
    public function bookmarkedByUsers()
    {
        return $this->belongsToMany(User::class, 'post_bookmarks')->withTimestamps();
    }

    /**
     * Check if a user has liked this post.
     */
    public function isLikedBy($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    /**
     * Check if a user has bookmarked this post.
     */
    public function isBookmarkedBy($userId)
    {
        return $this->bookmarks()->where('user_id', $userId)->exists();
    }

    /**
     * Get like count attribute.
     */
    public function getLikeCountAttribute()
    {
        return $this->likes()->count();
    }

    /**
     * Get comment count attribute.
     */
    public function getCommentCountAttribute()
    {
        return $this->comments()->count();
    }

    /**
     * Scope for visible posts (not hidden).
     */
    public function scopeVisible($query)
    {
        return $query->where('hidden', false);
    }

    /**
     * Scope for posts by game.
     */
    public function scopeByGame($query, $game)
    {
        return $query->where('game', $game);
    }

    /**
     * Scope for newest posts.
     */
    public function scopeNewest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope for popular posts (by like count).
     */
    public function scopePopular($query)
    {
        return $query->withCount('likes')->orderBy('likes_count', 'desc');
    }
}
