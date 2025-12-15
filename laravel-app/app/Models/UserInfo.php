<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
    use HasFactory;

    protected $table = 'user_info';

    protected $fillable = [
        'user_id',
        'username',
        'profile_picture',
        'exp_points',
        'is_banned',
        'role',
    ];

    protected $casts = [
        'is_banned' => 'boolean',
        'exp_points' => 'integer',
    ];

    /**
     * Get the user that owns this info.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate user level based on EXP points.
     */
    public function getLevelAttribute()
    {
        return floor($this->exp_points / 100) + 1;
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is moderator.
     */
    public function isModerator()
    {
        return $this->role === 'moderator';
    }

    /**
     * Check if user has admin or moderator privileges.
     */
    public function isStaff()
    {
        return in_array($this->role, ['admin', 'moderator']);
    }
}
