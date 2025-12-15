<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'message',
        'is_read',
        'related_user_id',
        'post_id',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * Get the user this notification belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related user (who triggered the notification).
     */
    public function relatedUser()
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }

    /**
     * Get the related post.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Scope for unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
}
