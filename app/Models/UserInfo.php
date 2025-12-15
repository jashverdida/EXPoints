<?php

namespace App\Models;

class UserInfo extends SupabaseModel
{
    protected static string $table = 'user_info';

    /**
     * Get the associated user.
     */
    public function getUser(): ?User
    {
        return User::find($this->user_id);
    }

    /**
     * Find by user ID.
     */
    public static function findByUserId(int $userId): ?static
    {
        $data = static::getService()->findBy(static::$table, 'user_id', $userId);

        if ($data) {
            $model = new static($data);
            $model->exists = true;
            return $model;
        }

        return null;
    }

    /**
     * Find by username.
     */
    public static function findByUsername(string $username): ?static
    {
        $data = static::getService()->findBy(static::$table, 'username', $username);

        if ($data) {
            $model = new static($data);
            $model->exists = true;
            return $model;
        }

        return null;
    }

    /**
     * Check if user is banned.
     */
    public function isBanned(): bool
    {
        return (bool) $this->is_banned;
    }

    /**
     * Get full name.
     */
    public function getFullName(): string
    {
        $parts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix
        ]);

        return implode(' ', $parts);
    }

    /**
     * Add experience points.
     */
    public function addExp(int $amount): bool
    {
        $this->exp_points = ($this->exp_points ?? 0) + $amount;
        return $this->save();
    }

    /**
     * Calculate level from EXP.
     * Formula: Level = 2 + floor((exp - 1) / 10) for exp > 1
     */
    public function getLevel(): int
    {
        $exp = $this->exp_points ?? 0;

        if ($exp <= 1) {
            return 1;
        }

        return 2 + floor(($exp - 1) / 10);
    }

    /**
     * Get EXP needed for next level.
     */
    public function getExpForNextLevel(): int
    {
        $currentLevel = $this->getLevel();

        if ($currentLevel < 2) {
            return 1;
        }

        return 1 + ($currentLevel - 1) * 10;
    }

    /**
     * Get progress percentage to next level.
     */
    public function getLevelProgress(): float
    {
        $exp = $this->exp_points ?? 0;
        $currentLevelExp = $this->getExpForNextLevel() - 10;
        $nextLevelExp = $this->getExpForNextLevel();

        if ($exp <= 1) {
            return $exp * 100;
        }

        $progress = (($exp - $currentLevelExp) / 10) * 100;

        return min(100, max(0, $progress));
    }
}
