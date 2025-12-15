<?php

namespace App\Services;

use App\Models\UserInfo;
use App\Models\Notification;
use App\Models\PostLike;
use App\Models\CommentLike;

class ExpSystemService
{
    // EXP conversion rate: 1 like = 5 EXP
    public const EXP_PER_LIKE = 5;

    /**
     * Calculate level from EXP.
     * Formula: Level 1 for exp <= 1, then Level = 2 + floor((exp - 1) / 10)
     */
    public function calculateLevel(int $exp): int
    {
        if ($exp <= 1) {
            return 1;
        }

        return 2 + floor(($exp - 1) / 10);
    }

    /**
     * Calculate EXP needed to reach a level.
     */
    public function expForLevel(int $level): int
    {
        if ($level <= 1) {
            return 0;
        }

        if ($level == 2) {
            return 1;
        }

        return 1 + ($level - 2) * 10;
    }

    /**
     * Calculate EXP needed for next level.
     */
    public function expForNextLevel(int $currentExp): int
    {
        $currentLevel = $this->calculateLevel($currentExp);
        return $this->expForLevel($currentLevel + 1);
    }

    /**
     * Get progress percentage to next level.
     */
    public function getLevelProgress(int $exp): float
    {
        $currentLevel = $this->calculateLevel($exp);
        $currentLevelExp = $this->expForLevel($currentLevel);
        $nextLevelExp = $this->expForLevel($currentLevel + 1);

        if ($nextLevelExp == $currentLevelExp) {
            return 100;
        }

        $progress = (($exp - $currentLevelExp) / ($nextLevelExp - $currentLevelExp)) * 100;

        return min(100, max(0, $progress));
    }

    /**
     * Add EXP to a user and check for level up.
     */
    public function addExp(int $userId, int $amount): array
    {
        $userInfo = UserInfo::findByUserId($userId);

        if (!$userInfo) {
            return [
                'success' => false,
                'message' => 'User info not found'
            ];
        }

        $oldExp = $userInfo->exp_points ?? 0;
        $oldLevel = $this->calculateLevel($oldExp);

        $newExp = $oldExp + $amount;
        $newLevel = $this->calculateLevel($newExp);

        $userInfo->exp_points = $newExp;
        $userInfo->save();

        $leveledUp = $newLevel > $oldLevel;

        // Create level up notification if user leveled up
        if ($leveledUp) {
            $this->createLevelUpNotification($userId, $newLevel);
        }

        return [
            'success' => true,
            'old_exp' => $oldExp,
            'new_exp' => $newExp,
            'old_level' => $oldLevel,
            'new_level' => $newLevel,
            'leveled_up' => $leveledUp
        ];
    }

    /**
     * Remove EXP from a user (e.g., when a like is removed).
     */
    public function removeExp(int $userId, int $amount): array
    {
        $userInfo = UserInfo::findByUserId($userId);

        if (!$userInfo) {
            return [
                'success' => false,
                'message' => 'User info not found'
            ];
        }

        $oldExp = $userInfo->exp_points ?? 0;
        $newExp = max(0, $oldExp - $amount);

        $userInfo->exp_points = $newExp;
        $userInfo->save();

        return [
            'success' => true,
            'old_exp' => $oldExp,
            'new_exp' => $newExp,
            'level' => $this->calculateLevel($newExp)
        ];
    }

    /**
     * Recalculate total EXP for a user based on all their likes received.
     */
    public function recalculateExp(int $userId): array
    {
        $userInfo = UserInfo::findByUserId($userId);

        if (!$userInfo) {
            return [
                'success' => false,
                'message' => 'User info not found'
            ];
        }

        // Count all post likes received
        $postLikes = PostLike::query()
            ->whereIn('post_id', function ($query) use ($userId) {
                // This would need a subquery - simplified version
                return [];
            })
            ->count();

        // For simplicity, we'll just count likes from posts table
        // In a real implementation, you'd sum likes from posts where user_id = $userId
        $totalLikes = 0; // TODO: Implement proper counting

        $totalExp = $totalLikes * self::EXP_PER_LIKE;

        $userInfo->exp_points = $totalExp;
        $userInfo->save();

        return [
            'success' => true,
            'total_likes' => $totalLikes,
            'total_exp' => $totalExp,
            'level' => $this->calculateLevel($totalExp)
        ];
    }

    /**
     * Create a level up notification.
     */
    protected function createLevelUpNotification(int $userId, int $newLevel): void
    {
        $messages = [
            2 => "Congratulations! You've reached Level 2! Keep up the great reviews!",
            5 => "Amazing! You've hit Level 5! You're becoming a trusted reviewer!",
            10 => "Incredible! Level 10 achieved! You're a seasoned game critic!",
            20 => "Legendary! Level 20! Your reviews are truly valued by the community!",
            50 => "EPIC! Level 50! You're one of the most experienced reviewers!",
        ];

        $message = $messages[$newLevel] ?? "Level Up! You've reached Level {$newLevel}!";

        Notification::createLevelUpNotification($userId, $newLevel, $message);
    }

    /**
     * Get user stats.
     */
    public function getUserStats(int $userId): array
    {
        $userInfo = UserInfo::findByUserId($userId);

        if (!$userInfo) {
            return [
                'exp' => 0,
                'level' => 1,
                'progress' => 0,
                'exp_for_next_level' => 1
            ];
        }

        $exp = $userInfo->exp_points ?? 0;

        return [
            'exp' => $exp,
            'level' => $this->calculateLevel($exp),
            'progress' => $this->getLevelProgress($exp),
            'exp_for_next_level' => $this->expForNextLevel($exp)
        ];
    }
}
