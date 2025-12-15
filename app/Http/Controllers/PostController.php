<?php

namespace App\Http\Controllers;

use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Exception;

class PostController extends Controller
{
    protected SupabaseService $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    /**
     * Store a new post/review
     */
    public function store(Request $request)
    {
        $request->validate([
            'game' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $userId = session('user_id');
        $username = session('username');
        $userEmail = session('user_email');

        try {
            $postData = [
                'user_id' => $userId,
                'username' => $username,
                'user_email' => $userEmail,
                'game' => $request->input('game'),
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'hidden' => 0,
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ];

            $post = $this->supabase->insert('posts', $postData);

            // Update user EXP
            $this->updateUserExp($userId, 5); // 5 EXP for posting

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Post created successfully',
                    'post' => $post
                ]);
            }

            return redirect()->route('dashboard')->with('success', 'Your review has been posted!');

        } catch (Exception $e) {
            \Log::error("Post creation error: " . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to create post'], 500);
            }

            return back()->with('error', 'Failed to create post. Please try again.');
        }
    }

    /**
     * Show a single post
     */
    public function show($id)
    {
        try {
            $post = $this->supabase->find('posts', $id);

            if (!$post) {
                return redirect()->route('dashboard')->with('error', 'Post not found');
            }

            // Get author info
            $authorInfo = $this->supabase->findBy('user_info', 'username', $post['username']);

            // Get comments
            $comments = $this->supabase->select(
                'post_comments',
                '*',
                ['post_id' => $id],
                ['order' => 'created_at.asc']
            );

            // Enrich comments with user info
            foreach ($comments as &$comment) {
                $commentUserInfo = $this->supabase->findBy('user_info', 'user_id', $comment['user_id']);
                $comment['profile_picture'] = $commentUserInfo['profile_picture'] ?? '/assets/img/cat1.jpg';
            }

            // Get like count
            $likeCount = $this->supabase->count('post_likes', ['post_id' => $id]);

            // Check if user liked
            $userId = session('user_id');
            $userLiked = false;
            if ($userId) {
                $like = $this->supabase->select('post_likes', 'id', [
                    'post_id' => $id,
                    'user_id' => $userId
                ], ['limit' => 1]);
                $userLiked = !empty($like);
            }

            // Check if bookmarked
            $isBookmarked = false;
            if ($userId) {
                $bookmark = $this->supabase->select('bookmarks', 'id', [
                    'post_id' => $id,
                    'user_id' => $userId
                ], ['limit' => 1]);
                $isBookmarked = !empty($bookmark);
            }

            return view('posts.show', [
                'post' => $post,
                'authorInfo' => $authorInfo,
                'comments' => $comments,
                'likeCount' => $likeCount,
                'userLiked' => $userLiked,
                'isBookmarked' => $isBookmarked,
                'userProfilePicture' => session('profile_picture', '/assets/img/cat1.jpg'),
            ]);

        } catch (Exception $e) {
            \Log::error("Post show error: " . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Failed to load post');
        }
    }

    /**
     * Like/unlike a post
     */
    public function toggleLike(Request $request, $id)
    {
        $userId = session('user_id');

        try {
            // Check if already liked
            $existingLike = $this->supabase->select('post_likes', '*', [
                'post_id' => $id,
                'user_id' => $userId
            ], ['limit' => 1]);

            if (!empty($existingLike)) {
                // Unlike
                $this->supabase->delete('post_likes', [
                    'post_id' => $id,
                    'user_id' => $userId
                ]);
                $liked = false;
            } else {
                // Like
                $this->supabase->insert('post_likes', [
                    'post_id' => (int)$id,
                    'user_id' => $userId,
                    'created_at' => now()->toIso8601String(),
                ]);
                $liked = true;

                // Give EXP to post author
                $post = $this->supabase->find('posts', $id);
                if ($post && $post['user_id'] !== $userId) {
                    $this->updateUserExp($post['user_id'], 1);
                }
            }

            $likeCount = $this->supabase->count('post_likes', ['post_id' => $id]);

            return response()->json([
                'success' => true,
                'liked' => $liked,
                'count' => $likeCount
            ]);

        } catch (Exception $e) {
            \Log::error("Like toggle error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to toggle like'], 500);
        }
    }

    /**
     * Add comment to post
     */
    public function addComment(Request $request, $id)
    {
        $request->validate([
            'text' => 'required|string|max:1000',
        ]);

        $userId = session('user_id');
        $username = session('username');

        try {
            $commentData = [
                'post_id' => (int)$id,
                'user_id' => $userId,
                'username' => $username,
                'text' => $request->input('text'),
                'created_at' => now()->toIso8601String(),
            ];

            $comment = $this->supabase->insert('post_comments', $commentData);

            // Give EXP
            $this->updateUserExp($userId, 2);

            // Get user info for response
            $userInfo = $this->supabase->findBy('user_info', 'user_id', $userId);

            return response()->json([
                'success' => true,
                'comment' => array_merge($comment, [
                    'profile_picture' => $userInfo['profile_picture'] ?? '/assets/img/cat1.jpg'
                ])
            ]);

        } catch (Exception $e) {
            \Log::error("Comment add error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to add comment'], 500);
        }
    }

    /**
     * Toggle bookmark
     */
    public function toggleBookmark(Request $request, $id)
    {
        $userId = session('user_id');

        try {
            $existingBookmark = $this->supabase->select('bookmarks', '*', [
                'post_id' => $id,
                'user_id' => $userId
            ], ['limit' => 1]);

            if (!empty($existingBookmark)) {
                $this->supabase->delete('bookmarks', [
                    'post_id' => $id,
                    'user_id' => $userId
                ]);
                $bookmarked = false;
            } else {
                $this->supabase->insert('bookmarks', [
                    'post_id' => (int)$id,
                    'user_id' => $userId,
                    'created_at' => now()->toIso8601String(),
                ]);
                $bookmarked = true;
            }

            return response()->json([
                'success' => true,
                'bookmarked' => $bookmarked
            ]);

        } catch (Exception $e) {
            \Log::error("Bookmark toggle error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to toggle bookmark'], 500);
        }
    }

    /**
     * Delete a post
     */
    public function destroy($id)
    {
        $userId = session('user_id');
        $userRole = session('user_role');

        try {
            $post = $this->supabase->find('posts', $id);

            if (!$post) {
                return response()->json(['error' => 'Post not found'], 404);
            }

            // Check permission
            if ($post['user_id'] !== $userId && !in_array($userRole, ['admin', 'mod'])) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Delete related data
            $this->supabase->delete('post_likes', ['post_id' => $id]);
            $this->supabase->delete('post_comments', ['post_id' => $id]);
            $this->supabase->delete('bookmarks', ['post_id' => $id]);
            $this->supabase->deleteById('posts', $id);

            return response()->json(['success' => true]);

        } catch (Exception $e) {
            \Log::error("Post delete error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to delete post'], 500);
        }
    }

    /**
     * Update user EXP points
     */
    protected function updateUserExp($userId, $points)
    {
        try {
            $userInfo = $this->supabase->findBy('user_info', 'user_id', $userId);
            if ($userInfo) {
                $newExp = ($userInfo['exp_points'] ?? 0) + $points;
                $this->supabase->update('user_info', ['exp_points' => $newExp], ['user_id' => $userId]);
            }
        } catch (Exception $e) {
            \Log::error("EXP update error: " . $e->getMessage());
        }
    }
}
