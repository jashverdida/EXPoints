<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Speed test - no Supabase calls
Route::get('/test-speed', function () {
    return response()->json([
        'status' => 'fast',
        'time' => now()->toDateTimeString(),
        'message' => 'If you see this instantly, Laravel is fast. Supabase API calls are slow.'
    ]);
});

// Clear session (for testing) - no middleware
Route::get('/clear-session', function (\Illuminate\Http\Request $request) {
    session()->flush();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // Clear any cookies
    $cookie = cookie()->forget(config('session.cookie'));

    return redirect()->route('login')
        ->with('success', 'Session cleared. You can now login.')
        ->withCookie($cookie);
});

// Debug session and user data
Route::get('/debug-session', function () {
    $supabase = app(\App\Services\SupabaseService::class);
    $userId = session('user_id');

    $data = [
        'session' => [
            'user_id' => $userId,
            'user_email' => session('user_email'),
            'username' => session('username'),
            'authenticated' => session('authenticated'),
        ],
        'user_info' => null,
        'user' => null,
        'error' => null,
    ];

    if ($userId) {
        try {
            $data['user_info'] = $supabase->findBy('user_info', 'user_id', $userId);
            $data['user'] = $supabase->find('users', $userId);
        } catch (\Exception $e) {
            $data['error'] = $e->getMessage();
        }
    }

    return response()->json($data, 200, [], JSON_PRETTY_PRINT);
});

// Debug user_info table
Route::get('/debug-users', function () {
    $supabase = app(\App\Services\SupabaseService::class);

    try {
        $userInfos = $supabase->select('user_info', 'username,profile_picture', [], ['limit' => 20]);
        return response()->json([
            'count' => count($userInfos),
            'users' => $userInfos,
        ], 200, [], JSON_PRETTY_PRINT);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Supabase connection test route
Route::get('/test-supabase', function () {
    $supabase = app(\App\Services\SupabaseService::class);

    try {
        $start = microtime(true);
        $users = $supabase->select('users', 'id', [], ['limit' => 1]);
        $time = round((microtime(true) - $start) * 1000);

        return response()->json([
            'status' => 'connected',
            'response_time_ms' => $time,
            'users_table' => !empty($users) ? 'has data' : 'empty',
            'supabase_url' => config('supabase.url'),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'supabase_url' => config('supabase.url'),
        ], 500);
    }
});

// Auth routes (guests only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Logout (authenticated only) - support both GET and POST
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth.supabase');

// Banned/Disabled pages
Route::get('/banned', [AuthController::class, 'banned'])->name('banned');
Route::get('/disabled', [AuthController::class, 'disabled'])->name('disabled');

// User authenticated routes
Route::middleware(['auth.supabase'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/popular', [DashboardController::class, 'popular'])->name('popular');
    Route::get('/newest', [DashboardController::class, 'newest'])->name('newest');
    Route::get('/bookmarks', [DashboardController::class, 'bookmarks'])->name('bookmarks');
    Route::get('/games', [DashboardController::class, 'games'])->name('games');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/picture', [ProfileController::class, 'updatePicture'])->name('profile.picture');
    Route::get('/user/{username}', [ProfileController::class, 'viewProfile'])->name('profile.view');

    // Posts routes
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{id}', [PostController::class, 'show'])->name('posts.show');
    Route::delete('/posts/{id}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::post('/posts/{id}/like', [PostController::class, 'toggleLike'])->name('posts.like');
    Route::post('/posts/{id}/bookmark', [PostController::class, 'toggleBookmark'])->name('posts.bookmark');
    Route::post('/posts/{id}/comment', [PostController::class, 'addComment'])->name('posts.comment');
});

// Moderator routes
Route::middleware(['auth.supabase', 'role:mod'])->prefix('mod')->name('mod.')->group(function () {
    Route::get('/dashboard', function () {
        return view('mod.dashboard');
    })->name('dashboard');
});

// Admin routes
Route::middleware(['auth.supabase', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/moderators', [AdminController::class, 'moderators'])->name('moderators');
    Route::get('/ban-appeals', [AdminController::class, 'banAppeals'])->name('ban-appeals');
    Route::post('/flag-ban', [AdminController::class, 'flagBan'])->name('flag-ban');
    Route::post('/users/{id}/ban', [AdminController::class, 'banUser'])->name('users.ban');
    Route::post('/users/{id}/unban', [AdminController::class, 'unbanUser'])->name('users.unban');
    Route::post('/users/{id}/disable', [AdminController::class, 'disableUser'])->name('users.disable');
    Route::post('/users/{id}/enable', [AdminController::class, 'enableUser'])->name('users.enable');
    Route::post('/users/{id}/role', [AdminController::class, 'updateRole'])->name('users.role');
    Route::post('/posts/{id}/visibility', [AdminController::class, 'togglePostVisibility'])->name('posts.visibility');
});
