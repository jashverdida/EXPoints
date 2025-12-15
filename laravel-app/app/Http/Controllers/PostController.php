<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostLike;
use App\Models\PostBookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * Display dashboard with posts.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userInfo = $user->userInfo;

        // Get search parameters
        $search = $request->input('search');
        $filter = $request->input('filter', 'title');

        // Query posts
        $query = Post::with(['authorInfo', 'user'])
            ->visible()
            ->withCount(['likes', 'comments'])
            ->newest();

        // Apply search if provided
        if ($search) {
            switch ($filter) {
                case 'author':
                    $query->where('username', 'like', "%{$search}%");
                    break;
                case 'content':
                    $query->where('content', 'like', "%{$search}%");
                    break;
                case 'title':
                default:
                    $query->where('title', 'like', "%{$search}%");
                    break;
            }
        }

        $posts = $query->limit(50)->get();

        // Add bookmark and like status for current user
        $posts->each(function ($post) use ($user) {
            $post->is_bookmarked = $post->isBookmarkedBy($user->id);
            $post->is_liked = $post->isLikedBy($user->id);
        });

        return view('dashboard', compact('posts', 'userInfo', 'search', 'filter'));
    }

    /**
     * Show newest posts page.
     */
    public function newest()
    {
        $user = Auth::user();
        $userInfo = $user->userInfo;

        $posts = Post::with(['authorInfo', 'user'])
            ->visible()
            ->withCount(['likes', 'comments'])
            ->newest()
            ->limit(100)
            ->get();

        $posts->each(function ($post) use ($user) {
            $post->is_bookmarked = $post->isBookmarkedBy($user->id);
            $post->is_liked = $post->isLikedBy($user->id);
        });

        return view('posts.newest', compact('posts', 'userInfo'));
    }

    /**
     * Show popular posts page.
     */
    public function popular()
    {
        $user = Auth::user();
        $userInfo = $user->userInfo;

        $posts = Post::with(['authorInfo', 'user'])
            ->visible()
            ->withCount(['likes', 'comments'])
            ->popular()
            ->limit(100)
            ->get();

        $posts->each(function ($post) use ($user) {
            $post->is_bookmarked = $post->isBookmarkedBy($user->id);
            $post->is_liked = $post->isLikedBy($user->id);
        });

        return view('posts.popular', compact('posts', 'userInfo'));
    }

    /**
     * Show bookmarked posts page.
     */
    public function bookmarks()
    {
        $user = Auth::user();
        $userInfo = $user->userInfo;

        $posts = $user->bookmarkedPosts()
            ->with(['authorInfo', 'user'])
            ->visible()
            ->withCount(['likes', 'comments'])
            ->orderBy('post_bookmarks.created_at', 'desc')
            ->limit(100)
            ->get();

        $posts->each(function ($post) use ($user) {
            $post->is_bookmarked = true; // Already bookmarked
            $post->is_liked = $post->isLikedBy($user->id);
        });

        return view('posts.bookmarks', compact('posts', 'userInfo'));
    }

    /**
     * Show posts by game.
     */
    public function byGame($game)
    {
        $user = Auth::user();
        $userInfo = $user->userInfo;

        $posts = Post::with(['authorInfo', 'user'])
            ->visible()
            ->byGame($game)
            ->withCount(['likes', 'comments'])
            ->newest()
            ->limit(100)
            ->get();

        $posts->each(function ($post) use ($user) {
            $post->is_bookmarked = $post->isBookmarkedBy($user->id);
            $post->is_liked = $post->isLikedBy($user->id);
        });

        return view('posts.by-game', compact('posts', 'userInfo', 'game'));
    }

    /**
     * Store a new post.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'game' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $userInfo = $user->userInfo;

        $post = Post::create([
            'user_id' => $user->id,
            'username' => $userInfo->username,
            'game' => $validated['game'],
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        // Award EXP for posting
        $userInfo->increment('exp_points', 10);

        return response()->json([
            'success' => true,
            'message' => 'Post created successfully!',
            'post_id' => $post->id,
        ]);
    }

    /**
     * Update a post.
     */
    public function update(Request $request, Post $post)
    {
        // Check ownership
        if ($post->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to edit this post.',
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $post->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully!',
        ]);
    }

    /**
     * Delete a post.
     */
    public function destroy(Post $post)
    {
        // Check ownership
        if ($post->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to delete this post.',
            ], 403);
        }

        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully!',
        ]);
    }

    /**
     * Toggle like on a post.
     */
    public function toggleLike(Post $post)
    {
        $userId = Auth::id();

        $like = PostLike::where('post_id', $post->id)
            ->where('user_id', $userId)
            ->first();

        if ($like) {
            // Unlike
            $like->delete();
            $message = 'Post unliked';
        } else {
            // Like
            PostLike::create([
                'post_id' => $post->id,
                'user_id' => $userId,
            ]);
            $message = 'Post liked';

            // Award EXP to post author
            if ($post->user_id !== $userId) {
                $post->user->userInfo->increment('exp_points', 2);
            }
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'like_count' => $post->likes()->count(),
        ]);
    }

    /**
     * Toggle bookmark on a post.
     */
    public function toggleBookmark(Post $post)
    {
        $userId = Auth::id();

        $bookmark = PostBookmark::where('post_id', $post->id)
            ->where('user_id', $userId)
            ->first();

        if ($bookmark) {
            // Remove bookmark
            $bookmark->delete();
            $message = 'Bookmark removed';
        } else {
            // Add bookmark
            PostBookmark::create([
                'post_id' => $post->id,
                'user_id' => $userId,
            ]);
            $message = 'Post bookmarked';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }
}
