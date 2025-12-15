<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // Post operations
    Route::prefix('posts')->group(function () {
        Route::post('/', [PostController::class, 'store']);
        Route::put('/{post}', [PostController::class, 'update']);
        Route::delete('/{post}', [PostController::class, 'destroy']);
        Route::post('/{post}/like', [PostController::class, 'toggleLike']);
        Route::post('/{post}/bookmark', [PostController::class, 'toggleBookmark']);
        
        // Comments
        Route::get('/{post}/comments', [CommentController::class, 'index']);
        Route::post('/{post}/comments', [CommentController::class, 'store']);
    });
    
    // Comment operations
    Route::prefix('comments')->group(function () {
        Route::put('/{comment}', [CommentController::class, 'update']);
        Route::delete('/{comment}', [CommentController::class, 'destroy']);
        Route::post('/{comment}/like', [CommentController::class, 'toggleLike']);
    });
});
