<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\CommentLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Get comments for a post.
     */
    public function index(Post $post)
    {
        $userId = Auth::id();

        $comments = $post->comments()
            ->with(['authorInfo', 'user'])
            ->withCount('likes')
            ->get();

        // Add like status for current user
        $comments->each(function ($comment) use ($userId) {
            $comment->user_liked = $comment->isLikedBy($userId);
        });

        return response()->json([
            'success' => true,
            'comments' => $comments,
        ]);
    }

    /**
     * Store a new comment.
     */
    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'comment_text' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        $userInfo = $user->userInfo;

        $comment = PostComment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'username' => $userInfo->username,
            'comment_text' => $validated['comment_text'],
        ]);

        // Award EXP for commenting
        $userInfo->increment('exp_points', 5);

        // Award EXP to post author
        if ($post->user_id !== $user->id) {
            $post->user->userInfo->increment('exp_points', 3);
        }

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully!',
            'comment' => $comment->load(['authorInfo']),
        ]);
    }

    /**
     * Update a comment.
     */
    public function update(Request $request, PostComment $comment)
    {
        // Check ownership
        if ($comment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to edit this comment.',
            ], 403);
        }

        $validated = $request->validate([
            'comment_text' => 'required|string|max:1000',
        ]);

        $comment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully!',
        ]);
    }

    /**
     * Delete a comment.
     */
    public function destroy(PostComment $comment)
    {
        // Check ownership
        if ($comment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to delete this comment.',
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully!',
        ]);
    }

    /**
     * Toggle like on a comment.
     */
    public function toggleLike(PostComment $comment)
    {
        $userId = Auth::id();

        $like = CommentLike::where('comment_id', $comment->id)
            ->where('user_id', $userId)
            ->first();

        if ($like) {
            // Unlike
            $like->delete();
            $message = 'Comment unliked';
        } else {
            // Like
            CommentLike::create([
                'comment_id' => $comment->id,
                'user_id' => $userId,
            ]);
            $message = 'Comment liked';

            // Award EXP to comment author
            if ($comment->user_id !== $userId) {
                $comment->user->userInfo->increment('exp_points', 1);
            }
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'like_count' => $comment->likes()->count(),
        ]);
    }
}
