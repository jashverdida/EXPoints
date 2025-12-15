<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\UserInfo;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get all posts with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 15);
        $game = $request->get('game');

        $filters = [];
        if ($game) {
            $filters['game'] = $game;
        }

        $posts = Post::paginated($page, $perPage, $filters);

        return response()->json([
            'data' => $posts,
            'page' => $page,
            'per_page' => $perPage,
            'has_more' => count($posts) === $perPage
        ]);
    }

    /**
     * Get a single post.
     */
    public function show(int $id): JsonResponse
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $user = Auth::user();
        $postData = $post->toArray();
        $postData['is_liked'] = $user ? $post->isLikedBy($user->id) : false;
        $postData['is_bookmarked'] = $user ? $post->isBookmarkedBy($user->id) : false;

        return response()->json($postData);
    }

    /**
     * Create a new post.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'game' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $userInfo = UserInfo::findByUserId($user->id);

        $post = Post::create([
            'user_id' => $user->id,
            'username' => $userInfo ? $userInfo->username : 'Anonymous',
            'title' => $request->title,
            'content' => $request->content,
            'game' => $request->game,
            'likes' => 0,
            'comments' => 0,
            'hidden' => 0
        ]);

        return response()->json($post, 201);
    }

    /**
     * Update a post.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $user = Auth::user();

        // Check ownership
        if ($post->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'game' => 'sometimes|string|max:255',
        ]);

        if ($request->has('title')) {
            $post->title = $request->title;
        }
        if ($request->has('content')) {
            $post->content = $request->content;
        }
        if ($request->has('game')) {
            $post->game = $request->game;
        }

        $post->save();

        return response()->json($post);
    }

    /**
     * Delete a post.
     */
    public function destroy(int $id): JsonResponse
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $user = Auth::user();

        // Check ownership
        if ($post->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }

    /**
     * Toggle like on a post.
     */
    public function like(int $id): JsonResponse
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $user = Auth::user();
        $liked = $post->toggleLike($user->id);

        if ($liked) {
            // Notify post owner about the like
            $this->notificationService->notifyPostLike($post->user_id, $user->id, $post->id);
        } else {
            // Remove EXP when unlike
            $this->notificationService->handlePostUnlike($post->user_id, $user->id);
        }

        return response()->json([
            'liked' => $liked,
            'like_count' => $post->likes
        ]);
    }

    /**
     * Toggle bookmark on a post.
     */
    public function bookmark(int $id): JsonResponse
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $user = Auth::user();
        $bookmarked = $post->toggleBookmark($user->id);

        return response()->json([
            'bookmarked' => $bookmarked
        ]);
    }

    /**
     * Get popular posts.
     */
    public function popular(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $posts = Post::popular($limit);

        return response()->json($posts);
    }

    /**
     * Get newest posts.
     */
    public function newest(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $posts = Post::newest($limit);

        return response()->json($posts);
    }

    /**
     * Search posts.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2'
        ]);

        $posts = Post::search($request->q, $request->get('limit', 20));

        return response()->json($posts);
    }

    /**
     * Get posts by game.
     */
    public function byGame(Request $request, string $game): JsonResponse
    {
        $posts = Post::byGame($game, $request->get('limit', 20));

        return response()->json($posts);
    }
}
