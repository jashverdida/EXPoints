<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ModerationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/popular', [PostController::class, 'popular']);
Route::get('/posts/newest', [PostController::class, 'newest']);
Route::get('/posts/search', [PostController::class, 'search']);
Route::get('/posts/game/{game}', [PostController::class, 'byGame']);
Route::get('/posts/{id}', [PostController::class, 'show']);
Route::get('/posts/{postId}/comments', [CommentController::class, 'index']);
Route::get('/comments/{commentId}/replies', [CommentController::class, 'replies']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::get('/users/username/{username}', [UserController::class, 'showByUsername']);
Route::get('/users/{id}/stats', [UserController::class, 'stats']);
Route::get('/users/{id}/posts', [UserController::class, 'posts']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Current user
    Route::get('/user', [UserController::class, 'me']);
    Route::put('/user', function (Request $request) {
        return app(UserController::class)->update($request, $request->user()->id);
    });
    Route::get('/user/bookmarks', [UserController::class, 'bookmarks']);

    // Posts
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
    Route::post('/posts/{id}/like', [PostController::class, 'like']);
    Route::post('/posts/{id}/bookmark', [PostController::class, 'bookmark']);

    // Comments
    Route::post('/posts/{postId}/comments', [CommentController::class, 'store']);
    Route::put('/comments/{commentId}', [CommentController::class, 'update']);
    Route::delete('/comments/{commentId}', [CommentController::class, 'destroy']);
    Route::post('/comments/{commentId}/like', [CommentController::class, 'like']);
    Route::post('/comments/{commentId}/reply', [CommentController::class, 'reply']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/count', [NotificationController::class, 'count']);
    Route::post('/notifications/{notificationId}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);

    // Users
    Route::put('/users/{id}', [UserController::class, 'update']);

    // Moderation (mod/admin only)
    Route::middleware('role:mod,admin')->group(function () {
        Route::post('/posts/{postId}/hide', [ModerationController::class, 'hide']);
        Route::post('/posts/{postId}/unhide', [ModerationController::class, 'unhide']);
        Route::post('/users/{userId}/flag-ban', [ModerationController::class, 'flagBan']);
        Route::post('/users/{userId}/unban', [ModerationController::class, 'unban']);
        Route::get('/moderation/log', [ModerationController::class, 'log']);
    });

    // Admin only
    Route::middleware('role:admin')->group(function () {
        Route::get('/moderation/ban-reviews', [ModerationController::class, 'pendingBanReviews']);
        Route::post('/moderation/ban-reviews/{reviewId}/approve', [ModerationController::class, 'approveBanReview']);
        Route::post('/moderation/ban-reviews/{reviewId}/reject', [ModerationController::class, 'rejectBanReview']);
        Route::post('/users/{userId}/ban', [ModerationController::class, 'ban']);
        Route::post('/users/{userId}/disable', [ModerationController::class, 'disableUser']);
        Route::post('/users/{userId}/enable', [ModerationController::class, 'enableUser']);
    });
});
