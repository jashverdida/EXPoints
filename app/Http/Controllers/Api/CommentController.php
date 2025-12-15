<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\UserInfo;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get comments for a post.
     */
    public function index(int $postId): JsonResponse
    {
        try {
            $post = Post::find($postId);

            if (!$post) {
                return response()->json(['success' => false, 'error' => 'Post not found'], 404);
            }

            $comments = PostComment::forPost($postId);

            // Add like status and profile pictures for authenticated user
            $user = Auth::user();
            $commentsData = array_map(function ($comment) use ($user) {
                $data = $comment->toArray();
                $data['is_liked'] = $user ? $comment->isLikedBy($user->id) : false;
                $data['reply_count'] = $comment->getReplyCount();

                // Get profile picture from user_info
                $userInfo = UserInfo::findByUserId($comment->user_id);
                $data['profile_picture'] = $userInfo ? $userInfo->profile_picture : '/assets/img/cat1.jpg';
                $data['commenter_profile_picture'] = $data['profile_picture'];
                $data['exp_points'] = $userInfo ? ($userInfo->exp_points ?? 0) : 0;

                return $data;
            }, $comments);

            return response()->json([
                'success' => true,
                'comments' => $commentsData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'comments' => []
            ]);
        }
    }

    /**
     * Create a new comment.
     */
    public function store(Request $request, int $postId): JsonResponse
    {
        $post = Post::find($postId);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        $user = Auth::user();
        $userInfo = UserInfo::findByUserId($user->id);

        $comment = PostComment::create([
            'post_id' => $postId,
            'user_id' => $user->id,
            'username' => $userInfo ? $userInfo->username : 'Anonymous',
            'comment' => $request->comment,
            'like_count' => 0,
            'reply_count' => 0
        ]);

        // Update comment count on post
        $post->comments = ($post->comments ?? 0) + 1;
        $post->save();

        // Notify post owner
        $this->notificationService->notifyPostComment($post->user_id, $user->id, $postId);

        return response()->json($comment, 201);
    }

    /**
     * Update a comment.
     */
    public function update(Request $request, int $commentId): JsonResponse
    {
        $comment = PostComment::find($commentId);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        $user = Auth::user();

        // Check ownership
        if ($comment->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        $comment->comment = $request->comment;
        $comment->save();

        return response()->json($comment);
    }

    /**
     * Delete a comment.
     */
    public function destroy(int $commentId): JsonResponse
    {
        $comment = PostComment::find($commentId);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        $user = Auth::user();

        // Check ownership
        if ($comment->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $postId = $comment->post_id;
        $comment->delete();

        // Update comment count on post
        $post = Post::find($postId);
        if ($post) {
            $post->comments = max(0, ($post->comments ?? 0) - 1);
            $post->save();
        }

        return response()->json(['message' => 'Comment deleted successfully']);
    }

    /**
     * Toggle like on a comment.
     */
    public function like(int $commentId): JsonResponse
    {
        $comment = PostComment::find($commentId);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        $user = Auth::user();
        $liked = $comment->toggleLike($user->id);

        if ($liked) {
            // Notify comment owner about the like
            $this->notificationService->notifyCommentLike($comment->user_id, $user->id, $comment->post_id);
        } else {
            // Remove EXP when unlike
            $this->notificationService->handleCommentUnlike($comment->user_id, $user->id);
        }

        return response()->json([
            'liked' => $liked,
            'like_count' => $comment->like_count
        ]);
    }

    /**
     * Get replies to a comment.
     */
    public function replies(int $commentId): JsonResponse
    {
        $comment = PostComment::find($commentId);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        $replies = $comment->getReplies();

        $user = Auth::user();
        $repliesData = array_map(function ($reply) use ($user) {
            $data = $reply->toArray();
            $data['is_liked'] = $user ? $reply->isLikedBy($user->id) : false;
            return $data;
        }, $replies);

        return response()->json($repliesData);
    }

    /**
     * Create a reply to a comment.
     */
    public function reply(Request $request, int $commentId): JsonResponse
    {
        $parentComment = PostComment::find($commentId);

        if (!$parentComment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        $user = Auth::user();
        $userInfo = UserInfo::findByUserId($user->id);

        $reply = $parentComment->addReply(
            $user->id,
            $userInfo ? $userInfo->username : 'Anonymous',
            $request->comment
        );

        // Notify parent comment owner
        $this->notificationService->notifyCommentReply(
            $parentComment->user_id,
            $user->id,
            $parentComment->post_id
        );

        return response()->json($reply, 201);
    }
}
