<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'username',
        'comment_text',
    ];

    /**
     * Get the post this comment belongs to.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user who wrote this comment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user info for this comment's author.
     */
    public function authorInfo()
    {
        return $this->hasOneThrough(
            UserInfo::class,
            User::class,
            'id',
            'user_id',
            'user_id',
            'id'
        );
    }

    /**
     * Get all likes for this comment.
     */
    public function likes()
    {
        return $this->hasMany(CommentLike::class, 'comment_id');
    }

    /**
     * Get users who liked this comment.
     */
    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'comment_likes', 'comment_id', 'user_id')->withTimestamps();
    }

    /**
     * Check if a user has liked this comment.
     */
    public function isLikedBy($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    /**
     * Get like count attribute.
     */
    public function getLikeCountAttribute()
    {
        return $this->likes()->count();
    }
}
