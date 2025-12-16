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
            // DEBUG: Check Supabase configuration
            $supabaseUrl = config('supabase.url');
            $hasKeys = !empty(config('supabase.anon_key')) && !empty(config('supabase.service_key'));
            
            if (empty($supabaseUrl) || !$hasKeys) {
                throw new Exception("Supabase not configured. URL: $supabaseUrl, Has Keys: " . ($hasKeys ? 'Yes' : 'No'));
            }

            // Get stats with error handling
            try {
                $totalUsers = $this->supabase->count('users');
            } catch (Exception $e) {
                \Log::error("Count users error: " . $e->getMessage());
                $totalUsers = 0;
            }

            try {
                $totalPosts = $this->supabase->count('posts');
            } catch (Exception $e) {
                \Log::error("Count posts error: " . $e->getMessage());
                $totalPosts = 0;
            }

            try {
                $totalComments = $this->supabase->count('post_comments');
            } catch (Exception $e) {
                \Log::error("Count comments error: " . $e->getMessage());
                $totalComments = 0;
            }

            try {
                $totalAdmins = $this->supabase->count('users', ['role' => 'admin']);
            } catch (Exception $e) {
                \Log::error("Count admins error: " . $e->getMessage());
                $totalAdmins = 0;
            }

            try {
                $bannedUsers = $this->supabase->count('user_info', ['is_banned' => true]);
            } catch (Exception $e) {
                \Log::error("Count banned error: " . $e->getMessage());
                $bannedUsers = 0;
            }

            try {
                $disabledUsers = $this->supabase->count('users', ['is_disabled' => true]);
            } catch (Exception $e) {
                \Log::error("Count disabled error: " . $e->getMessage());
                $disabledUsers = 0;
            }

            // Get recent users
            try {
                $recentUsers = $this->supabase->select('users', '*', [], [
                    'order' => 'created_at.desc',
                    'limit' => 10
                ]);
                \Log::info("Fetched " . count($recentUsers) . " users");
            } catch (Exception $e) {
                \Log::error("Fetch users error: " . $e->getMessage());
                $recentUsers = [];
            }

            // Enrich with user_info
            foreach ($recentUsers as &$user) {
                try {
                    $userInfo = $this->supabase->findBy('user_info', 'user_id', $user['id']);
                    $user['username'] = $userInfo['username'] ?? $user['email'];
                    $user['profile_picture'] = $userInfo['profile_picture'] ?? '/assets/img/cat1.jpg';
                    $user['is_banned'] = $userInfo['is_banned'] ?? false;
                } catch (Exception $e) {
                    \Log::error("Enrich user {$user['id']} error: " . $e->getMessage());
                    $user['username'] = $user['email'];
                    $user['profile_picture'] = '/assets/img/cat1.jpg';
                    $user['is_banned'] = false;
                }
            }

            // Get recent posts for moderation (50 posts)
            try {
                $recentPosts = $this->supabase->select('posts', '*', [], [
                    'order' => 'created_at.desc',
                    'limit' => 50
                ]);
                \Log::info("Fetched " . count($recentPosts) . " posts from Supabase");
                
                if (!empty($recentPosts)) {
                    \Log::info("First post: " . json_encode($recentPosts[0]));
                }
            } catch (Exception $e) {
                \Log::error("Fetch posts error: " . $e->getMessage());
                \Log::error("Stack: " . $e->getTraceAsString());
                $recentPosts = [];
            }

            // Enrich posts with username
            foreach ($recentPosts as &$post) {
                try {
                    $userInfo = $this->supabase->findBy('user_info', 'user_id', $post['user_id']);
                    $post['username'] = $userInfo['username'] ?? 'Unknown';
                    $post['profile_picture'] = $userInfo['profile_picture'] ?? '/assets/img/cat1.jpg';
                } catch (Exception $e) {
                    \Log::error("Enrich post {$post['id']} error: " . $e->getMessage());
                    $post['username'] = 'Unknown';
                    $post['profile_picture'] = '/assets/img/cat1.jpg';
                }
            }

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
            return view('admin.dashboard', [
                'totalUsers' => 0,
                'totalPosts' => 0,
                'totalComments' => 0,
                'totalAdmins' => 0,
                'bannedUsers' => 0,
                'disabledUsers' => 0,
                'recentUsers' => [],
                'recentPosts' => [],
                'error' => 'Failed to load dashboard data',
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
                'is_banned' => 1,  // smallint, not boolean
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
                'is_banned' => 0,  // smallint, not boolean
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
                'is_disabled' => 1,  // smallint, not boolean
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
                'is_disabled' => 0,  // smallint, not boolean
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
     * Manage administrators (not moderators)
     */
    public function moderators()
    {
        try {
            // Only fetch admin role users
            $admins = $this->supabase->select('users', '*', ['role' => 'admin']);

            foreach ($admins as &$admin) {
                $userInfo = $this->supabase->findBy('user_info', 'user_id', $admin['id']);
                $admin['username'] = $userInfo['username'] ?? $admin['email'];
                $admin['profile_picture'] = $userInfo['profile_picture'] ?? '/assets/img/cat1.jpg';
            }

            return view('admin.moderators', [
                'moderators' => $admins,  // Keeping variable name for compatibility with view
            ]);

        } catch (Exception $e) {
            \Log::error("Moderators list error: " . $e->getMessage());
            return view('admin.moderators', [
                'moderators' => [],
                'error' => 'Failed to load administrators',
            ]);
        }
    }

    /**
     * Create a new administrator
     * Optimized: Skip duplicate checks and let Supabase handle unique constraints
     */
    public function createAdmin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'username' => 'required|string|min:3|max:50',
            'password' => 'required|string|min:6',
        ]);

        $email = $request->input('email');
        $username = $request->input('username');
        $password = $request->input('password');

        try {
            // Create user with admin role - let DB constraints handle duplicates
            $userData = [
                'email' => $email,
                'password' => $password,
                'role' => 'admin',
                'created_at' => now()->toIso8601String(),
            ];

            \Log::info("Creating admin user: $email");
            $newUser = $this->supabase->insert('users', $userData);
            \Log::info("User insert result: " . json_encode($newUser));

            if (!$newUser || !isset($newUser['id'])) {
                \Log::error("Failed to create user - no ID returned");
                return response()->json(['success' => false, 'error' => 'Failed to create account'], 500);
            }

            // Create user_info record
            $userInfoData = [
                'user_id' => $newUser['id'],
                'username' => $username,
                'first_name' => '',
                'middle_name' => '',
                'last_name' => '',
                'suffix' => '',
                'bio' => '',
                'profile_picture' => '/assets/img/cat1.jpg',
                'exp_points' => 0,
                'is_banned' => 0,  // smallint, not boolean
                'created_at' => now()->toIso8601String(),
            ];

            \Log::info("Creating user_info for user: " . $newUser['id']);
            $this->supabase->insert('user_info', $userInfoData);

            return response()->json(['success' => true, 'message' => 'Administrator created successfully']);

        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            \Log::error("Create admin error: " . $errorMsg);

            // Check for duplicate key errors from Supabase
            if (str_contains($errorMsg, 'duplicate') || str_contains($errorMsg, 'unique') || str_contains($errorMsg, '23505')) {
                if (str_contains($errorMsg, 'email')) {
                    return response()->json(['success' => false, 'error' => 'Email already registered'], 422);
                }
                if (str_contains($errorMsg, 'username')) {
                    return response()->json(['success' => false, 'error' => 'Username already taken'], 422);
                }
                return response()->json(['success' => false, 'error' => 'User already exists'], 422);
            }

            return response()->json(['success' => false, 'error' => 'Failed to create administrator: ' . $errorMsg], 500);
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
