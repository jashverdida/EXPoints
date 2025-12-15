<?php

namespace App\Http\Controllers;

use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Exception;

class ProfileController extends Controller
{
    protected SupabaseService $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    /**
     * Normalize profile picture path from database
     * Converts "..\assets\img\..." to "assets/img/..." (for use with asset() helper)
     */
    protected function normalizeProfilePicture(?string $path): string
    {
        $default = 'assets/img/cat1.jpg';  // No leading / for asset() helper

        if (empty($path)) {
            return $default;
        }

        // Convert backslashes to forward slashes
        $path = str_replace('\\', '/', $path);

        // Remove leading ".." or "../"
        $path = preg_replace('/^\.\.\//', '', $path);

        // Remove leading "/" if present (for asset() helper compatibility)
        $path = ltrim($path, '/');

        return $path;
    }

    /**
     * Display the user's profile
     */
    public function show()
    {
        $userId = session('user_id');
        $username = session('username', 'User');

        // Debug: Check if user is logged in
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Please login first');
        }

        try {
            // Get user info
            $userInfo = $this->supabase->findBy('user_info', 'user_id', $userId);
            $user = $this->supabase->find('users', $userId);

            if (!$userInfo) {
                // Create user_info if it doesn't exist
                \Log::warning("No user_info found for user_id: $userId, attempting to create...");

                // Try to get user email to create profile
                if ($user) {
                    $this->supabase->insert('user_info', [
                        'user_id' => $userId,
                        'username' => $username,
                        'profile_picture' => '/assets/img/cat1.jpg',
                        'exp_points' => 0,
                        'bio' => '',
                        'is_banned' => 0,
                        'created_at' => now()->toIso8601String(),
                    ]);

                    // Retry fetching
                    $userInfo = $this->supabase->findBy('user_info', 'user_id', $userId);
                }

                if (!$userInfo) {
                    return redirect()->route('dashboard')->with('error', 'Profile not found. User ID: ' . $userId);
                }
            }

            // Get user's posts
            $posts = $this->supabase->select('posts', '*', ['username' => $userInfo['username']], [
                'order' => 'created_at.desc',
                'limit' => 20
            ]);

            // Get post counts
            $postCount = count($posts);

            // Get total likes received and enrich posts with counts
            $totalLikes = 0;
            foreach ($posts as &$post) {
                $likeCount = $this->supabase->count('post_likes', ['post_id' => $post['id']]);
                $commentCount = $this->supabase->count('post_comments', ['post_id' => $post['id']]);
                $post['likes'] = $likeCount;
                $post['like_count'] = $likeCount;
                $post['comment_count'] = $commentCount;
                $totalLikes += $likeCount;
            }

            // Get best posts (top 3 by likes)
            $bestPosts = $posts;
            usort($bestPosts, fn($a, $b) => ($b['like_count'] ?? 0) - ($a['like_count'] ?? 0));
            $bestPosts = array_slice($bestPosts, 0, 3);

            // Calculate level from exp_points
            $expPoints = $userInfo['exp_points'] ?? 0;
            $level = floor($expPoints / 100) + 1;
            $expProgress = $expPoints % 100;

            // Build display name
            $firstName = $userInfo['first_name'] ?? '';
            $lastName = $userInfo['last_name'] ?? '';
            $displayName = trim($firstName . ' ' . $lastName);
            if (empty($displayName)) {
                $displayName = $userInfo['username'] ?? $username;
            }

            // Format start date
            $startedDate = 'Unknown';
            if (!empty($user['created_at'])) {
                $startedDate = \Carbon\Carbon::parse($user['created_at'])->format('M Y');
            }

            return view('profile.show', [
                'user' => $user,
                'userInfo' => $userInfo,
                'username' => $userInfo['username'] ?? $username,
                'displayName' => $displayName,
                'fullName' => trim($firstName . ' ' . $lastName),
                'handle' => '@' . ($userInfo['username'] ?? $username),
                'email' => $user['email'] ?? '',
                'profilePicture' => $this->normalizeProfilePicture($userInfo['profile_picture'] ?? null),
                'bio' => $userInfo['bio'] ?? '',
                'firstName' => $firstName,
                'lastName' => $lastName,
                'expPoints' => $expPoints,
                'level' => $level,
                'levelProgress' => $expProgress,
                'posts' => $posts,
                'bestPosts' => $bestPosts,
                'totalStars' => $totalLikes,
                'totalReviews' => $postCount,
                'startedDate' => $startedDate,
                'favoriteGame' => $userInfo['favorite_game'] ?? '',
                'genres' => $userInfo['genres'] ?? '',
            ]);

        } catch (Exception $e) {
            \Log::error("Profile show error: " . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Failed to load profile');
        }
    }

    /**
     * Display the profile edit form
     */
    public function edit()
    {
        $userId = session('user_id');

        try {
            $userInfo = $this->supabase->findBy('user_info', 'user_id', $userId);
            $user = $this->supabase->find('users', $userId);

            return view('profile.edit', [
                'user' => $user,
                'userInfo' => $userInfo,
                'username' => $userInfo['username'] ?? '',
                'email' => $user['email'] ?? '',
                'profilePicture' => $this->normalizeProfilePicture($userInfo['profile_picture'] ?? null),
                'bio' => $userInfo['bio'] ?? '',
                'firstName' => $userInfo['first_name'] ?? '',
                'middleName' => $userInfo['middle_name'] ?? '',
                'lastName' => $userInfo['last_name'] ?? '',
                'suffix' => $userInfo['suffix'] ?? '',
                'favoriteGame' => $userInfo['favorite_game'] ?? '',
                'genres' => $userInfo['genres'] ?? '',
            ]);

        } catch (Exception $e) {
            \Log::error("Profile edit error: " . $e->getMessage());
            return redirect()->route('profile.show')->with('error', 'Failed to load profile');
        }
    }

    /**
     * Update the user's profile
     */
    public function update(Request $request)
    {
        $request->validate([
            'username' => 'required|string|min:3|max:50',
            'bio' => 'nullable|string|max:500',
            'first_name' => 'nullable|string|max:50',
            'middle_name' => 'nullable|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'favorite_game' => 'nullable|string|max:100',
            'genres' => 'nullable|string|max:200',
        ]);

        $userId = session('user_id');
        $isAjax = $request->ajax() || $request->wantsJson();

        try {
            $currentUserInfo = $this->supabase->findBy('user_info', 'user_id', $userId);
            $newUsername = $request->input('username');

            // Check if username changed and if new username is taken
            if ($currentUserInfo && $currentUserInfo['username'] !== $newUsername) {
                $existingUser = $this->supabase->findBy('user_info', 'username', $newUsername);
                if ($existingUser && $existingUser['user_id'] != $userId) {
                    if ($isAjax) {
                        return response()->json(['success' => false, 'error' => 'Username already taken'], 422);
                    }
                    return back()->with('error', 'Username already taken');
                }
            }

            // Update user_info
            $this->supabase->update('user_info', [
                'username' => $newUsername,
                'bio' => $request->input('bio', ''),
                'first_name' => $request->input('first_name', ''),
                'middle_name' => $request->input('middle_name', ''),
                'last_name' => $request->input('last_name', ''),
                'favorite_game' => $request->input('favorite_game', ''),
                'genres' => $request->input('genres', ''),
            ], ['user_id' => $userId]);

            // Update session username
            session(['username' => $newUsername]);

            if ($isAjax) {
                return response()->json(['success' => true]);
            }
            return redirect()->route('profile.show')->with('success', 'Profile updated successfully');

        } catch (Exception $e) {
            \Log::error("Profile update error: " . $e->getMessage());
            if ($isAjax) {
                return response()->json(['success' => false, 'error' => 'Failed to update profile'], 500);
            }
            return back()->with('error', 'Failed to update profile');
        }
    }

    /**
     * Update profile picture
     */
    public function updatePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|string',
        ]);

        $userId = session('user_id');
        $isAjax = $request->ajax() || $request->wantsJson();

        try {
            $this->supabase->update('user_info', [
                'profile_picture' => $request->input('profile_picture'),
            ], ['user_id' => $userId]);

            if ($isAjax) {
                return response()->json(['success' => true]);
            }
            return redirect()->route('profile.show')->with('success', 'Profile picture updated');

        } catch (Exception $e) {
            \Log::error("Profile picture update error: " . $e->getMessage());
            if ($isAjax) {
                return response()->json(['success' => false, 'error' => 'Failed to update picture'], 500);
            }
            return back()->with('error', 'Failed to update profile picture');
        }
    }

    /**
     * View another user's profile
     */
    public function viewProfile($username)
    {
        try {
            $userInfo = $this->supabase->findBy('user_info', 'username', $username);

            if (!$userInfo) {
                return redirect()->route('dashboard')->with('error', 'User not found');
            }

            $user = $this->supabase->find('users', $userInfo['user_id']);

            // Check if user is banned
            if (!empty($userInfo['is_banned'])) {
                return redirect()->route('dashboard')->with('error', 'This user has been banned');
            }

            // Get user's posts
            $posts = $this->supabase->select('posts', '*', ['username' => $username, 'hidden' => 0], [
                'order' => 'created_at.desc',
                'limit' => 20
            ]);

            $postCount = count($posts);
            $totalLikes = 0;

            foreach ($posts as &$post) {
                $likeCount = $this->supabase->count('post_likes', ['post_id' => $post['id']]);
                $post['likes'] = $likeCount;
                $totalLikes += $likeCount;
            }

            $expPoints = $userInfo['exp_points'] ?? 0;
            $level = floor($expPoints / 100) + 1;

            return view('profile.view', [
                'userInfo' => $userInfo,
                'username' => $userInfo['username'],
                'profilePicture' => $this->normalizeProfilePicture($userInfo['profile_picture'] ?? null),
                'bio' => $userInfo['bio'] ?? '',
                'firstName' => $userInfo['first_name'] ?? '',
                'lastName' => $userInfo['last_name'] ?? '',
                'expPoints' => $expPoints,
                'level' => $level,
                'posts' => $posts,
                'postCount' => $postCount,
                'totalLikes' => $totalLikes,
                'createdAt' => $user['created_at'] ?? null,
                'favoriteGame' => $userInfo['favorite_game'] ?? '',
                'isOwnProfile' => session('user_id') == $userInfo['user_id'],
            ]);

        } catch (Exception $e) {
            \Log::error("View profile error: " . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Failed to load profile');
        }
    }
}
