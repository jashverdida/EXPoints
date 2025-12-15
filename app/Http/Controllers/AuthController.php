<?php

namespace App\Http\Controllers;

use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Exception;

class AuthController extends Controller
{
    protected SupabaseService $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    /**
     * Show login form
     */
    public function showLogin()
    {
        // Redirect if already authenticated
        if (session('authenticated') === true) {
            return $this->redirectBasedOnRole(session('user_role', 'user'));
        }

        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        try {
            // Query user from Supabase
            $users = $this->supabase->select('users', '*', ['email' => $email], ['limit' => 1]);

            if (empty($users)) {
                return back()->withInput()->with('error', 'Invalid email or password');
            }

            $user = $users[0];

            // Direct password comparison (matching existing system)
            if ($password !== $user['password']) {
                return back()->withInput()->with('error', 'Invalid email or password');
            }

            $role = $user['role'] ?? 'user';

            // Check if account is disabled
            if (!empty($user['is_disabled']) && $user['is_disabled'] == 1) {
                Session::put('disabled_reason', $user['disabled_reason'] ?? 'Your account has been disabled by an administrator.');
                Session::put('disabled_at', $user['disabled_at'] ?? null);
                Session::put('disabled_by', $user['disabled_by'] ?? null);
                return redirect()->route('disabled');
            }

            // Get username and ban status from user_info table
            $userInfo = $this->supabase->findBy('user_info', 'user_id', $user['id']);

            $username = $user['email']; // Default to email
            $isBanned = false;

            if ($userInfo) {
                $username = $userInfo['username'] ?? $user['email'];
                $isBanned = !empty($userInfo['is_banned']) && $userInfo['is_banned'] == 1;

                if ($isBanned) {
                    Session::put('ban_reason', $userInfo['ban_reason'] ?? '');
                    Session::put('banned_at', $userInfo['banned_at'] ?? '');
                    Session::put('banned_by', $userInfo['banned_by'] ?? '');
                    return redirect()->route('banned');
                }
            }

            // Set session variables
            Session::put('user_id', $user['id']);
            Session::put('user_email', $user['email']);
            Session::put('username', $username);
            Session::put('user_role', $role);
            Session::put('authenticated', true);
            Session::put('login_time', time());

            // Redirect based on role
            return $this->redirectBasedOnRole($role);

        } catch (Exception $e) {
            \Log::error("Login error: " . $e->getMessage());
            return back()->withInput()->with('error', 'Database connection failed. Please try again later.');
        }
    }

    /**
     * Show registration form
     */
    public function showRegister()
    {
        if (session('authenticated') === true) {
            return $this->redirectBasedOnRole(session('user_role', 'user'));
        }

        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'username' => 'required|string|min:3|max:50',
            'first_name' => 'nullable|string|max:50',
            'last_name' => 'nullable|string|max:50',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');
        $username = $request->input('username');
        $firstName = $request->input('first_name', '');
        $lastName = $request->input('last_name', '');

        try {
            // Check if email already exists
            $existingUser = $this->supabase->findBy('users', 'email', $email);
            if ($existingUser) {
                return back()->withInput()->with('error', 'Email already registered');
            }

            // Check if username already exists
            $existingUsername = $this->supabase->findBy('user_info', 'username', $username);
            if ($existingUsername) {
                return back()->withInput()->with('error', 'Username already taken');
            }

            // Create user in users table
            $userData = [
                'email' => $email,
                'password' => $password, // Plain text (matching existing system)
                'role' => 'user',
                'created_at' => now()->toIso8601String(),
            ];

            $newUser = $this->supabase->insert('users', $userData);

            if (!$newUser || !isset($newUser['id'])) {
                return back()->withInput()->with('error', 'Failed to create account');
            }

            // Create user_info record
            $userInfoData = [
                'user_id' => $newUser['id'],
                'username' => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'bio' => '',
                'profile_picture' => '/assets/img/cat1.jpg',
                'exp_points' => 0,
                'is_banned' => false,
                'created_at' => now()->toIso8601String(),
            ];

            $this->supabase->insert('user_info', $userInfoData);

            // Auto login after registration
            Session::put('user_id', $newUser['id']);
            Session::put('user_email', $email);
            Session::put('username', $username);
            Session::put('user_role', 'user');
            Session::put('authenticated', true);
            Session::put('login_time', time());

            return redirect()->route('dashboard')->with('success', 'Account created successfully!');

        } catch (Exception $e) {
            \Log::error("Registration error: " . $e->getMessage());
            return back()->withInput()->with('error', 'Registration failed. Please try again later.');
        }
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Session::flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }

    /**
     * Show banned page
     */
    public function banned()
    {
        return view('auth.banned', [
            'reason' => session('ban_reason', ''),
            'bannedAt' => session('banned_at', ''),
            'bannedBy' => session('banned_by', ''),
        ]);
    }

    /**
     * Show disabled page
     */
    public function disabled()
    {
        return view('auth.disabled', [
            'reason' => session('disabled_reason', ''),
            'disabledAt' => session('disabled_at', ''),
            'disabledBy' => session('disabled_by', ''),
        ]);
    }

    /**
     * Redirect user based on their role
     */
    protected function redirectBasedOnRole(string $role)
    {
        return match($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'mod' => redirect()->route('mod.dashboard'),
            default => redirect()->route('dashboard'),
        };
    }
}
