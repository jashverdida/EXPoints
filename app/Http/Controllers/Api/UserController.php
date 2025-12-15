<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserInfo;
use App\Models\Post;
use App\Models\PostBookmark;
use App\Services\ExpSystemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected ExpSystemService $expService;

    public function __construct(ExpSystemService $expService)
    {
        $this->expService = $expService;
    }

    /**
     * Get user profile by ID.
     */
    public function show(int $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $userInfo = UserInfo::findByUserId($id);

        if (!$userInfo) {
            return response()->json(['message' => 'User info not found'], 404);
        }

        $stats = $this->expService->getUserStats($id);

        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'username' => $userInfo->username,
            'first_name' => $userInfo->first_name,
            'last_name' => $userInfo->last_name,
            'bio' => $userInfo->bio,
            'profile_picture' => $userInfo->profile_picture,
            'exp_points' => $stats['exp'],
            'level' => $stats['level'],
            'level_progress' => $stats['progress'],
            'created_at' => $user->created_at ?? $userInfo->created_at
        ]);
    }

    /**
     * Get current authenticated user.
     */
    public function me(): JsonResponse
    {
        $user = Auth::user();
        return $this->show($user->id);
    }

    /**
     * Update user profile.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();

        // Check if user is updating their own profile or is admin
        if ($user->id !== $id && $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'first_name' => 'sometimes|nullable|string|max:50',
            'last_name' => 'sometimes|nullable|string|max:50',
            'bio' => 'sometimes|nullable|string|max:500',
            'profile_picture' => 'sometimes|nullable|string|max:255'
        ]);

        $userInfo = UserInfo::findByUserId($id);

        if (!$userInfo) {
            return response()->json(['message' => 'User info not found'], 404);
        }

        if ($request->has('first_name')) {
            $userInfo->first_name = $request->first_name;
        }
        if ($request->has('last_name')) {
            $userInfo->last_name = $request->last_name;
        }
        if ($request->has('bio')) {
            $userInfo->bio = $request->bio;
        }
        if ($request->has('profile_picture')) {
            $userInfo->profile_picture = $request->profile_picture;
        }

        $userInfo->save();

        return $this->show($id);
    }

    /**
     * Get user's EXP and level stats.
     */
    public function stats(int $id): JsonResponse
    {
        $stats = $this->expService->getUserStats($id);

        return response()->json($stats);
    }

    /**
     * Get user's posts.
     */
    public function posts(int $id): JsonResponse
    {
        $posts = Post::byUser($id);

        return response()->json(array_map(function ($post) {
            return $post->toArray();
        }, $posts));
    }

    /**
     * Get current user's bookmarked posts.
     */
    public function bookmarks(): JsonResponse
    {
        $user = Auth::user();
        $posts = PostBookmark::getBookmarkedPosts($user->id);

        return response()->json(array_map(function ($post) {
            return $post->toArray();
        }, $posts));
    }

    /**
     * Get user by username.
     */
    public function showByUsername(string $username): JsonResponse
    {
        $userInfo = UserInfo::findByUsername($username);

        if (!$userInfo) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return $this->show($userInfo->user_id);
    }
}
