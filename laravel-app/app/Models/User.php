<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's info.
     */
    public function userInfo()
    {
        return $this->hasOne(UserInfo::class);
    }

    /**
     * Get all posts by this user.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get all comments by this user.
     */
    public function comments()
    {
        return $this->hasMany(PostComment::class);
    }

    /**
     * Get all likes by this user.
     */
    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    /**
     * Get all bookmarks by this user.
     */
    public function bookmarks()
    {
        return $this->hasMany(PostBookmark::class);
    }

    /**
     * Get posts this user has bookmarked.
     */
    public function bookmarkedPosts()
    {
        return $this->belongsToMany(Post::class, 'post_bookmarks')->withTimestamps();
    }

    /**
     * Get posts this user has liked.
     */
    public function likedPosts()
    {
        return $this->belongsToMany(Post::class, 'post_likes')->withTimestamps();
    }

    /**
     * Get all notifications for this user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get unread notifications.
     */
    public function unreadNotifications()
    {
        return $this->notifications()->where('is_read', false);
    }
}
