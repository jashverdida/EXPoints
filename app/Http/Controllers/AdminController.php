<?php

namespace App\Http\Controllers;

use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Exception;

class AdminController extends Controller
{
    protected SupabaseService $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    /**
     * Admin dashboard
     */
    public function dashboard()
    {
        try {
            // Get stats
            $totalUsers = $this->supabase->count('users');
            $totalPosts = $this->supabase->count('posts');
            $bannedUsers = $this->supabase->count('user_info', ['is_banned' => true]);
            $disabledUsers = $this->supabase->count('users', ['is_disabled' => true]);

            // Get recent users
            $recentUsers = $this->supabase->select('users', '*', [], [
                'order' => 'created_at.desc',
                'limit' => 10
            ]);

            // Enrich with user_info
            foreach ($recentUsers as &$user) {
                $userInfo = $this->supabase->findBy('user_info', 'user_id', $user['id']);
                $user['username'] = $userInfo['username'] ?? $user['email'];
                $user['profile_picture'] = $userInfo['profile_picture'] ?? '/assets/img/cat1.jpg';
                $user['is_banned'] = $userInfo['is_banned'] ?? false;
            }

            // Get recent posts
            $recentPosts = $this->supabase->select('posts', '*', [], [
                'order' => 'created_at.desc',
                'limit' => 10
            ]);

            return view('admin.dashboard', [
                'totalUsers' => $totalUsers,
                'totalPosts' => $totalPosts,
                'bannedUsers' => $bannedUsers,
                'disabledUsers' => $disabledUsers,
                'recentUsers' => $recentUsers,
                'recentPosts' => $recentPosts,
            ]);

        } catch (Exception $e) {
            \Log::error("Admin dashboard error: " . $e->getMessage());
            return view('admin.dashboard', [
                'totalUsers' => 0,
                'totalPosts' => 0,
                'bannedUsers' => 0,
                'disabledUsers' => 0,
                'recentUsers' => [],
                'recentPosts' => [],
                'error' => 'Failed to load dashboard data',
            ]);
        }
    }

    /**
     * Manage users
     */
    public function users(Request $request)
    {
        try {
            $search = $request->input('search', '');

            $users = $this->supabase->select('users', '*', [], [
                'order' => 'created_at.desc',
                'limit' => 100
            ]);

            // Enrich with user_info
            foreach ($users as &$user) {
                $userInfo = $this->supabase->findBy('user_info', 'user_id', $user['id']);
                $user['username'] = $userInfo['username'] ?? $user['email'];
                $user['profile_picture'] = $userInfo['profile_picture'] ?? '/assets/img/cat1.jpg';
                $user['is_banned'] = $userInfo['is_banned'] ?? false;
                $user['ban_reason'] = $userInfo['ban_reason'] ?? '';
            }

            // Filter by search
            if (!empty($search)) {
                $users = array_filter($users, function($user) use ($search) {
                    $searchLower = strtolower($search);
                    return str_contains(strtolower($user['email']), $searchLower) ||
                           str_contains(strtolower($user['username']), $searchLower);
                });
                $users = array_values($users);
            }

            return view('admin.users', [
                'users' => $users,
                'search' => $search,
            ]);

        } catch (Exception $e) {
            \Log::error("Admin users error: " . $e->getMessage());
            return view('admin.users', [
                'users' => [],
                'search' => '',
                'error' => 'Failed to load users',
            ]);
        }
    }

    /**
     * Ban a user
     */
    public function banUser(Request $request, $userId)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $adminId = session('user_id');

            $this->supabase->update('user_info', [
                'is_banned' => true,
                'ban_reason' => $request->input('reason'),
                'banned_at' => now()->toIso8601String(),
                'banned_by' => $adminId,
            ], ['user_id' => $userId]);

            return response()->json(['success' => true]);

        } catch (Exception $e) {
            \Log::error("Ban user error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to ban user'], 500);
        }
    }

    /**
     * Unban a user
     */
    public function unbanUser($userId)
    {
        try {
            $this->supabase->update('user_info', [
                'is_banned' => false,
                'ban_reason' => null,
                'banned_at' => null,
                'banned_by' => null,
            ], ['user_id' => $userId]);

            return response()->json(['success' => true]);

        } catch (Exception $e) {
            \Log::error("Unban user error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to unban user'], 500);
        }
    }

    /**
     * Disable a user account
     */
    public function disableUser(Request $request, $userId)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $adminId = session('user_id');

            $this->supabase->update('users', [
                'is_disabled' => true,
                'disabled_reason' => $request->input('reason'),
                'disabled_at' => now()->toIso8601String(),
                'disabled_by' => $adminId,
            ], ['id' => $userId]);

            return response()->json(['success' => true]);

        } catch (Exception $e) {
            \Log::error("Disable user error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to disable user'], 500);
        }
    }

    /**
     * Enable a user account
     */
    public function enableUser($userId)
    {
        try {
            $this->supabase->update('users', [
                'is_disabled' => false,
                'disabled_reason' => null,
                'disabled_at' => null,
                'disabled_by' => null,
            ], ['id' => $userId]);

            return response()->json(['success' => true]);

        } catch (Exception $e) {
            \Log::error("Enable user error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to enable user'], 500);
        }
    }

    /**
     * Update user role
     */
    public function updateRole(Request $request, $userId)
    {
        $request->validate([
            'role' => 'required|in:user,mod,admin',
        ]);

        try {
            $this->supabase->update('users', [
                'role' => $request->input('role'),
            ], ['id' => $userId]);

            return response()->json(['success' => true]);

        } catch (Exception $e) {
            \Log::error("Update role error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to update role'], 500);
        }
    }

    /**
     * Manage moderators
     */
    public function moderators()
    {
        try {
            $moderators = $this->supabase->select('users', '*', ['role' => 'mod']);

            foreach ($moderators as &$mod) {
                $userInfo = $this->supabase->findBy('user_info', 'user_id', $mod['id']);
                $mod['username'] = $userInfo['username'] ?? $mod['email'];
                $mod['profile_picture'] = $userInfo['profile_picture'] ?? '/assets/img/cat1.jpg';
            }

            return view('admin.moderators', [
                'moderators' => $moderators,
            ]);

        } catch (Exception $e) {
            \Log::error("Moderators list error: " . $e->getMessage());
            return view('admin.moderators', [
                'moderators' => [],
                'error' => 'Failed to load moderators',
            ]);
        }
    }

    /**
     * Hide/unhide a post (moderation)
     */
    public function togglePostVisibility($postId)
    {
        try {
            $post = $this->supabase->find('posts', $postId);

            if (!$post) {
                return response()->json(['error' => 'Post not found'], 404);
            }

            $newHidden = !($post['hidden'] ?? false);
            $this->supabase->updateById('posts', $postId, ['hidden' => $newHidden]);

            return response()->json([
                'success' => true,
                'hidden' => $newHidden
            ]);

        } catch (Exception $e) {
            \Log::error("Toggle post visibility error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to update post'], 500);
        }
    }
}
