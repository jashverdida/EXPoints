<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'comment_id',
        'user_id',
    ];

    /**
     * Get the comment this like belongs to.
     */
    public function comment()
    {
        return $this->belongsTo(PostComment::class, 'comment_id');
    }

    /**
     * Get the user who made this like.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
