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
            // Get stats - using optimized count method
            $totalUsers = $this->supabase->count('users');
            \Log::info("Total users: " . $totalUsers);
            
            $totalPosts = $this->supabase->count('posts');
            \Log::info("Total posts: " . $totalPosts);
            
            $totalComments = $this->supabase->count('post_comments');
            $totalAdmins = $this->supabase->count('users', ['role' => 'admin']);
            $bannedUsers = $this->supabase->count('user_info', ['is_banned' => true]);
            $disabledUsers = $this->supabase->count('users', ['is_disabled' => true]);

            // Get recent users with joined user_info in ONE query
            $recentUsers = $this->supabase->select(
                'users', 
                'id,email,role,created_at,is_disabled,user_info(username,profile_picture,is_banned)',
                [],
                ['order' => 'created_at.desc', 'limit' => 10]
            );
            \Log::info("Recent users count: " . count($recentUsers));

            // Format user data
            foreach ($recentUsers as &$user) {
                $userInfo = $user['user_info'][0] ?? null;
                $user['username'] = $userInfo['username'] ?? $user['email'];
                $user['profile_picture'] = $userInfo['profile_picture'] ?? '/assets/img/cat1.jpg';
                $user['is_banned'] = $userInfo['is_banned'] ?? false;
                unset($user['user_info']); // Clean up
            }

            // Get recent posts - simplified query first
            $recentPosts = $this->supabase->select(
                'posts',
                '*',
                [],
                ['order' => 'created_at.desc', 'limit' => 50]
            );
            \Log::info("Recent posts fetched: " . count($recentPosts));
            \Log::info("Sample post data: " . json_encode(array_slice($recentPosts, 0, 2)));

            // Fetch user info for all posts in batch (more efficient)
            $userIds = array_unique(array_column($recentPosts, 'user_id'));
            $userInfoMap = [];
            
            if (!empty($userIds)) {
                $userInfos = $this->supabase->select('user_info', 'user_id,username,profile_picture', [
                    'user_id.in' => '(' . implode(',', $userIds) . ')'
                ]);
                
                foreach ($userInfos as $info) {
                    $userInfoMap[$info['user_id']] = $info;
                }
            }

            // Enrich posts with username
            foreach ($recentPosts as &$post) {
                $userInfo = $userInfoMap[$post['user_id']] ?? null;
                $post['username'] = $userInfo['username'] ?? 'Unknown';
                $post['profile_picture'] = $userInfo['profile_picture'] ?? '/assets/img/cat1.jpg';
            }

            \Log::info("Passing to view - Posts: " . count($recentPosts));

            return view('admin.dashboard', [
                'totalUsers' => $totalUsers,
                'totalPosts' => $totalPosts,
                'totalComments' => $totalComments,
                'totalAdmins' => $totalAdmins,
                'bannedUsers' => $bannedUsers,
                'disabledUsers' => $disabledUsers,
                'recentUsers' => $recentUsers,
                'recentPosts' => $recentPosts,
            ]);

        } catch (Exception $e) {
            \Log::error("Admin dashboard error: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
            return view('admin.dashboard', [
                'totalUsers' => 0,
                'totalPosts' => 0,
                'totalComments' => 0,
                'totalAdmins' => 0,
                'bannedUsers' => 0,
                'disabledUsers' => 0,
                'recentUsers' => [],
                'recentPosts' => [],
                'error' => 'Failed to load dashboard data: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Ban appeals page
     */
    public function banAppeals()
    {
        try {
            // Get users with active ban appeals (if you have a ban_appeals table)
            // For now, just show banned users
            $bannedUsers = $this->supabase->select('user_info', '*', ['is_banned' => true]);

            foreach ($bannedUsers as &$bannedUser) {
                $user = $this->supabase->find('users', $bannedUser['user_id']);
                $bannedUser['email'] = $user['email'] ?? '';
            }

            return view('admin.ban-appeals', [
                'bannedUsers' => $bannedUsers,
            ]);

        } catch (Exception $e) {
            \Log::error("Ban appeals error: " . $e->getMessage());
            return view('admin.ban-appeals', [
                'bannedUsers' => [],
                'error' => 'Failed to load ban appeals',
            ]);
        }
    }

    /**
     * Flag a user for ban (from post moderation)
     */
    public function flagBan(Request $request)
    {
        $request->validate([
            'post_id' => 'required|integer',
            'reason' => 'required|string|max:500',
        ]);

        try {
            $postId = $request->input('post_id');
            $reason = $request->input('reason');
            $adminId = session('user_id');

            // Get the post to find the user
            $post = $this->supabase->find('posts', $postId);

            if (!$post) {
                return response()->json(['success' => false, 'error' => 'Post not found'], 404);
            }

            $userId = $post['user_id'];

            // Ban the user
            $this->supabase->update('user_info', [
                'is_banned' => true,
                'ban_reason' => $reason,
                'banned_at' => now()->toIso8601String(),
                'banned_by' => $adminId,
            ], ['user_id' => $userId]);

            return response()->json(['success' => true]);

        } catch (Exception $e) {
            \Log::error("Flag ban error: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to ban user'], 500);
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
