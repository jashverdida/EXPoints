<?php
/**
 * Notification System
 * Handles creating and managing user notifications
 */

class NotificationSystem {
    
    /**
     * Create a notification
     * @param mysqli $db Database connection
     * @param int $userId User to notify
     * @param string $type Notification type (like, comment, level_up)
     * @param string $message Notification message
     * @param int|null $postId Related post ID
     * @param int|null $fromUserId User who triggered the notification
     * @return bool Success status
     */
    public static function createNotification($db, $userId, $type, $message, $postId = null, $fromUserId = null) {
        try {
            $stmt = $db->prepare("INSERT INTO notifications (user_id, type, message, post_id, from_user_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issii", $userId, $type, $message, $postId, $fromUserId);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        } catch (Exception $e) {
            error_log("Error creating notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create a like notification
     * @param mysqli $db Database connection
     * @param int $postAuthorId Post author's user ID
     * @param int $likerId User who liked the post
     * @param int $postId Post ID
     * @param string $postTitle Post title
     * @return bool Success status
     */
    public static function notifyLike($db, $postAuthorId, $likerId, $postId, $postTitle) {
        // Don't notify if user likes their own post
        if ($postAuthorId == $likerId) {
            return true;
        }
        
        // Get liker's username
        $stmt = $db->prepare("SELECT username FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $likerId);
        $stmt->execute();
        $result = $stmt->get_result();
        $likerUsername = $result->fetch_assoc()['username'] ?? 'Someone';
        $stmt->close();
        
        $message = "{$likerUsername} liked your post: \"{$postTitle}\"";
        return self::createNotification($db, $postAuthorId, 'like', $message, $postId, $likerId);
    }
    
    /**
     * Create a comment notification
     * @param mysqli $db Database connection
     * @param int $postAuthorId Post author's user ID
     * @param int $commenterId User who commented
     * @param int $postId Post ID
     * @param string $postTitle Post title
     * @return bool Success status
     */
    public static function notifyComment($db, $postAuthorId, $commenterId, $postId, $postTitle) {
        // Don't notify if user comments on their own post
        if ($postAuthorId == $commenterId) {
            return true;
        }
        
        // Get commenter's username
        $stmt = $db->prepare("SELECT username FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $commenterId);
        $stmt->execute();
        $result = $stmt->get_result();
        $commenterUsername = $result->fetch_assoc()['username'] ?? 'Someone';
        $stmt->close();
        
        $message = "{$commenterUsername} commented on your post: \"{$postTitle}\"";
        return self::createNotification($db, $postAuthorId, 'comment', $message, $postId, $commenterId);
    }
    
    /**
     * Create a level up notification
     * @param mysqli $db Database connection
     * @param int $userId User who leveled up
     * @param int $newLevel New level reached
     * @return bool Success status
     */
    public static function notifyLevelUp($db, $userId, $newLevel) {
        $message = "🎉 Congratulations! You've reached Level {$newLevel}!";
        return self::createNotification($db, $userId, 'level_up', $message, null, null);
    }
    
    /**
     * Get unread notifications for a user
     * @param mysqli $db Database connection
     * @param int $userId User ID
     * @param int $limit Maximum number of notifications to retrieve
     * @return array Array of notifications
     */
    public static function getUnreadNotifications($db, $userId, $limit = 10) {
        try {
            $stmt = $db->prepare("
                SELECT n.*, u.username as from_username, u.profile_picture as from_profile_picture
                FROM notifications n
                LEFT JOIN user_info u ON n.from_user_id = u.user_id
                WHERE n.user_id = ? AND n.is_read = FALSE
                ORDER BY n.created_at DESC
                LIMIT ?
            ");
            $stmt->bind_param("ii", $userId, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            $notifications = [];
            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }
            $stmt->close();
            return $notifications;
        } catch (Exception $e) {
            error_log("Error getting notifications: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get unread notification count
     * @param mysqli $db Database connection
     * @param int $userId User ID
     * @return int Number of unread notifications
     */
    public static function getUnreadCount($db, $userId) {
        try {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $count = $result->fetch_assoc()['count'] ?? 0;
            $stmt->close();
            return (int)$count;
        } catch (Exception $e) {
            error_log("Error getting notification count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Mark notification as read
     * @param mysqli $db Database connection
     * @param int $notificationId Notification ID
     * @return bool Success status
     */
    public static function markAsRead($db, $notificationId) {
        try {
            $stmt = $db->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ?");
            $stmt->bind_param("i", $notificationId);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        } catch (Exception $e) {
            error_log("Error marking notification as read: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mark all notifications as read for a user
     * @param mysqli $db Database connection
     * @param int $userId User ID
     * @return bool Success status
     */
    public static function markAllAsRead($db, $userId) {
        try {
            $stmt = $db->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ? AND is_read = FALSE");
            $stmt->bind_param("i", $userId);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        } catch (Exception $e) {
            error_log("Error marking all notifications as read: " . $e->getMessage());
            return false;
        }
    }
}
