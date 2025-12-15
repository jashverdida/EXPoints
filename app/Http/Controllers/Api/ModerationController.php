<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use App\Models\UserInfo;
use App\Models\ModerationLog;
use App\Models\BanReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModerationController extends Controller
{
    /**
     * Hide a post.
     */
    public function hide(Request $request, int $postId): JsonResponse
    {
        $post = Post::find($postId);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $user = Auth::user();
        $userInfo = UserInfo::findByUserId($user->id);
        $moderatorName = $userInfo ? $userInfo->username : 'Unknown';

        $request->validate([
            'reason' => 'sometimes|string|max:500'
        ]);

        $post->hide();

        // Log the moderation action
        ModerationLog::logHide($postId, $moderatorName, $request->reason);

        return response()->json([
            'success' => true,
            'message' => 'Post hidden successfully'
        ]);
    }

    /**
     * Unhide a post.
     */
    public function unhide(Request $request, int $postId): JsonResponse
    {
        $post = Post::find($postId);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $user = Auth::user();
        $userInfo = UserInfo::findByUserId($user->id);
        $moderatorName = $userInfo ? $userInfo->username : 'Unknown';

        $request->validate([
            'reason' => 'sometimes|string|max:500'
        ]);

        $post->unhide();

        // Log the moderation action
        ModerationLog::logUnhide($postId, $moderatorName, $request->reason);

        return response()->json([
            'success' => true,
            'message' => 'Post unhidden successfully'
        ]);
    }

    /**
     * Flag a user for ban review.
     */
    public function flagBan(Request $request, int $userId): JsonResponse
    {
        $targetUser = User::find($userId);

        if (!$targetUser) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $targetUserInfo = UserInfo::findByUserId($userId);

        if (!$targetUserInfo) {
            return response()->json(['message' => 'User info not found'], 404);
        }

        $request->validate([
            'reason' => 'required|string|max:500',
            'post_id' => 'sometimes|integer'
        ]);

        $user = Auth::user();
        $userInfo = UserInfo::findByUserId($user->id);
        $flaggedBy = $userInfo ? $userInfo->username : 'Unknown';

        // Create ban review request
        $banReview = BanReview::createRequest(
            $targetUserInfo->username,
            $request->post_id ?? 0,
            $flaggedBy,
            $request->reason
        );

        // Log the moderation action if post_id provided
        if ($request->post_id) {
            ModerationLog::logFlag($request->post_id, $flaggedBy, $request->reason);
        }

        return response()->json([
            'success' => true,
            'message' => 'User flagged for ban review',
            'ban_review_id' => $banReview->id
        ]);
    }

    /**
     * Ban a user (admin only).
     */
    public function ban(Request $request, int $userId): JsonResponse
    {
        $targetUser = User::find($userId);

        if (!$targetUser) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $targetUserInfo = UserInfo::findByUserId($userId);

        if (!$targetUserInfo) {
            return response()->json(['message' => 'User info not found'], 404);
        }

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $user = Auth::user();
        $userInfo = UserInfo::findByUserId($user->id);
        $bannedBy = $userInfo ? $userInfo->username : 'Unknown';

        // Ban the user
        $targetUserInfo->is_banned = 1;
        $targetUserInfo->ban_reason = $request->reason;
        $targetUserInfo->banned_at = date('Y-m-d H:i:s');
        $targetUserInfo->banned_by = $bannedBy;
        $targetUserInfo->save();

        return response()->json([
            'success' => true,
            'message' => 'User banned successfully'
        ]);
    }

    /**
     * Unban a user.
     */
    public function unban(int $userId): JsonResponse
    {
        $targetUserInfo = UserInfo::findByUserId($userId);

        if (!$targetUserInfo) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $targetUserInfo->is_banned = 0;
        $targetUserInfo->ban_reason = null;
        $targetUserInfo->banned_at = null;
        $targetUserInfo->banned_by = null;
        $targetUserInfo->save();

        return response()->json([
            'success' => true,
            'message' => 'User unbanned successfully'
        ]);
    }

    /**
     * Get pending ban reviews.
     */
    public function pendingBanReviews(): JsonResponse
    {
        $reviews = BanReview::pending();

        return response()->json(array_map(function ($review) {
            return $review->toArray();
        }, $reviews));
    }

    /**
     * Approve a ban review.
     */
    public function approveBanReview(Request $request, int $reviewId): JsonResponse
    {
        $review = BanReview::find($reviewId);

        if (!$review) {
            return response()->json(['message' => 'Ban review not found'], 404);
        }

        if (!$review->isPending()) {
            return response()->json(['message' => 'This review has already been processed'], 400);
        }

        $user = Auth::user();
        $userInfo = UserInfo::findByUserId($user->id);
        $reviewedBy = $userInfo ? $userInfo->username : 'Unknown';

        $review->approve($reviewedBy);

        // Ban the user
        $targetUserInfo = UserInfo::findByUsername($review->username);
        if ($targetUserInfo) {
            $targetUserInfo->is_banned = 1;
            $targetUserInfo->ban_reason = $review->reason;
            $targetUserInfo->banned_at = date('Y-m-d H:i:s');
            $targetUserInfo->banned_by = $reviewedBy;
            $targetUserInfo->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Ban review approved and user banned'
        ]);
    }

    /**
     * Reject a ban review.
     */
    public function rejectBanReview(int $reviewId): JsonResponse
    {
        $review = BanReview::find($reviewId);

        if (!$review) {
            return response()->json(['message' => 'Ban review not found'], 404);
        }

        if (!$review->isPending()) {
            return response()->json(['message' => 'This review has already been processed'], 400);
        }

        $user = Auth::user();
        $userInfo = UserInfo::findByUserId($user->id);
        $reviewedBy = $userInfo ? $userInfo->username : 'Unknown';

        $review->reject($reviewedBy);

        return response()->json([
            'success' => true,
            'message' => 'Ban review rejected'
        ]);
    }

    /**
     * Get moderation log.
     */
    public function log(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 50);
        $logs = ModerationLog::recent($limit);

        return response()->json(array_map(function ($log) {
            return $log->toArray();
        }, $logs));
    }

    /**
     * Disable a user account (admin only).
     */
    public function disableUser(Request $request, int $userId): JsonResponse
    {
        $targetUser = User::find($userId);

        if (!$targetUser) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $user = Auth::user();
        $userInfo = UserInfo::findByUserId($user->id);
        $disabledBy = $userInfo ? $userInfo->username : 'Unknown';

        $targetUser->is_disabled = 1;
        $targetUser->disabled_reason = $request->reason;
        $targetUser->disabled_at = date('Y-m-d H:i:s');
        $targetUser->disabled_by = $disabledBy;
        $targetUser->save();

        return response()->json([
            'success' => true,
            'message' => 'User account disabled'
        ]);
    }

    /**
     * Enable a user account (admin only).
     */
    public function enableUser(int $userId): JsonResponse
    {
        $targetUser = User::find($userId);

        if (!$targetUser) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $targetUser->is_disabled = 0;
        $targetUser->disabled_reason = null;
        $targetUser->disabled_at = null;
        $targetUser->disabled_by = null;
        $targetUser->save();

        return response()->json([
            'success' => true,
            'message' => 'User account enabled'
        ]);
    }
}
