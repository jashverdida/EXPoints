<?php
/**
 * EXP and Level Calculation System
 * 
 * Formula:
 * - 1 like = 5 EXP (on posts or comments)
 * - Level 1 to 2: 1 EXP needed
 * - Level 2+: 10 EXP per level
 */

class ExpSystem {
    
    /**
     * Calculate level from EXP points
     * @param int $exp Total EXP points
     * @return int Current level
     */
    public static function calculateLevel($exp) {
        if ($exp < 1) {
            return 1;
        }
        
        // Level 1 to 2 requires only 1 EXP
        if ($exp < 1) {
            return 1;
        }
        
        // After level 2, each level requires 10 EXP
        // Level 2 starts at 1 EXP
        // Level 3 starts at 11 EXP (1 + 10)
        // Level 4 starts at 21 EXP (1 + 10 + 10)
        // Formula: Level = 2 + floor((exp - 1) / 10)
        
        return 2 + floor(($exp - 1) / 10);
    }
    
    /**
     * Calculate EXP needed for next level
     * @param int $currentExp Current EXP points
     * @return int EXP needed for next level
     */
    public static function expToNextLevel($currentExp) {
        $currentLevel = self::calculateLevel($currentExp);
        
        if ($currentLevel === 1) {
            // Need 1 EXP to reach level 2
            return 1 - $currentExp;
        }
        
        // Calculate EXP needed for next level
        // Next level starts at: 1 + (currentLevel - 1) * 10
        $nextLevelExp = 1 + ($currentLevel - 1) * 10;
        return $nextLevelExp - $currentExp;
    }
    
    /**
     * Get total EXP from likes
     * @param mysqli $db Database connection
     * @param int $userId User ID
     * @return int Total EXP (likes * 5)
     */
    public static function calculateUserExp($db, $userId) {
        // Get likes on user's posts
        $postLikesQuery = "
            SELECT COUNT(*) as post_likes
            FROM post_likes pl
            INNER JOIN posts p ON pl.post_id = p.id
            WHERE p.user_id = ?
        ";
        $stmt = $db->prepare($postLikesQuery);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $postLikes = $result->fetch_assoc()['post_likes'] ?? 0;
        $stmt->close();
        
        // Check if comments table has user_id column
        $hasUserIdColumn = false;
        $columnsResult = $db->query("SHOW COLUMNS FROM comments LIKE 'user_id'");
        if (!$columnsResult) {
            // Try post_comments table
            $columnsResult = $db->query("SHOW COLUMNS FROM post_comments LIKE 'user_id'");
            $tableName = 'post_comments';
        } else {
            $tableName = 'comments';
        }
        
        if ($columnsResult && $columnsResult->num_rows > 0) {
            $hasUserIdColumn = true;
        }
        
        $commentLikes = 0;
        
        if ($hasUserIdColumn) {
            // Get likes on user's comments using user_id
            $commentLikesQuery = "
                SELECT COUNT(*) as comment_likes
                FROM comment_likes cl
                INNER JOIN $tableName c ON cl.comment_id = c.id
                WHERE c.user_id = ?
            ";
            $stmt = $db->prepare($commentLikesQuery);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $commentLikes = $result->fetch_assoc()['comment_likes'] ?? 0;
            $stmt->close();
        } else {
            // Fallback: Get likes on comments by username
            // First get username from user_id
            $usernameQuery = "SELECT username FROM user_info WHERE user_id = ?";
            $stmt = $db->prepare($usernameQuery);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $userData = $result->fetch_assoc();
            $username = $userData['username'] ?? null;
            $stmt->close();
            
            if ($username) {
                $commentLikesQuery = "
                    SELECT COUNT(*) as comment_likes
                    FROM comment_likes cl
                    INNER JOIN $tableName c ON cl.comment_id = c.id
                    WHERE c.username = ?
                ";
                $stmt = $db->prepare($commentLikesQuery);
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();
                $commentLikes = $result->fetch_assoc()['comment_likes'] ?? 0;
                $stmt->close();
            }
        }
        
        // Calculate total EXP: 1 like = 5 EXP
        $totalLikes = $postLikes + $commentLikes;
        $totalExp = $totalLikes * 5;
        
        return $totalExp;
    }
    
    /**
     * Update user's EXP and level in database
     * @param mysqli $db Database connection
     * @param int $userId User ID
     * @return array ['exp' => int, 'level' => int, 'likes' => int, 'leveled_up' => bool, 'old_level' => int]
     */
    public static function updateUserExp($db, $userId) {
        // Get current EXP to check for level up
        $oldExpQuery = "SELECT exp_points FROM user_info WHERE user_id = ?";
        $stmt = $db->prepare($oldExpQuery);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $oldExp = $result->fetch_assoc()['exp_points'] ?? 0;
        $stmt->close();
        
        $oldLevel = self::calculateLevel($oldExp);
        
        // Calculate EXP from likes
        $totalExp = self::calculateUserExp($db, $userId);
        
        // Calculate new level
        $newLevel = self::calculateLevel($totalExp);
        
        // Check if leveled up
        $leveledUp = $newLevel > $oldLevel;
        
        // Update user_info table
        $updateQuery = "UPDATE user_info SET exp_points = ? WHERE user_id = ?";
        $stmt = $db->prepare($updateQuery);
        $stmt->bind_param("ii", $totalExp, $userId);
        $stmt->execute();
        $stmt->close();
        
        return [
            'exp' => $totalExp,
            'level' => $newLevel,
            'likes' => $totalExp / 5, // Total likes
            'leveled_up' => $leveledUp,
            'old_level' => $oldLevel
        ];
    }
    
    /**
     * Get user's current stats
     * @param mysqli $db Database connection
     * @param int $userId User ID
     * @return array User stats
     */
    public static function getUserStats($db, $userId) {
        // Get current EXP from database
        $query = "SELECT exp_points FROM user_info WHERE user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();
        $stmt->close();
        
        $currentExp = $userData['exp_points'] ?? 0;
        $currentLevel = self::calculateLevel($currentExp);
        $expToNext = self::expToNextLevel($currentExp);
        
        return [
            'exp' => $currentExp,
            'level' => $currentLevel,
            'exp_to_next_level' => $expToNext,
            'total_likes' => $currentExp / 5
        ];
    }
}
?>
