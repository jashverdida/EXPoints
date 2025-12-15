<?php
/**
 * Supabase Service Class
 * Replaces FirestoreService with Supabase PostgreSQL operations
 * Uses Supabase REST API directly (no Composer dependency)
 */

require_once __DIR__ . '/env.php';

class SupabaseService {
    private $supabaseUrl;
    private $supabaseKey;
    private $headers;

    public function __construct() {
        $this->supabaseUrl = getenv('SUPABASE_URL');
        $this->supabaseKey = getenv('SUPABASE_SERVICE_KEY'); // Use service key for admin operations
        
        if (!$this->supabaseUrl || !$this->supabaseKey) {
            error_log("Supabase credentials missing - URL: " . ($this->supabaseUrl ? "SET" : "NOT SET") . ", Key: " . ($this->supabaseKey ? "SET" : "NOT SET"));
            throw new Exception('Supabase credentials not configured. Check .env file.');
        }

        $this->headers = [
            'apikey: ' . $this->supabaseKey,
            'Authorization: Bearer ' . $this->supabaseKey,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];
    }

    /**
     * Make HTTP request to Supabase REST API
     */
    private function request($method, $endpoint, $data = null) {
        $url = $this->supabaseUrl . '/rest/v1/' . $endpoint;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 400) {
            $error = json_decode($response, true);
            throw new Exception($error['message'] ?? 'Supabase API error: ' . $httpCode);
        }
        
        return json_decode($response, true);
    }

    // ==================== USER OPERATIONS ====================

    /**
     * Create new user profile
     */
    public function createUser($userId, $email, $displayName, $avatar = null) {
        try {
            $data = [
                'id' => $userId,
                'email' => $email,
                'display_name' => $displayName,
                'avatar_url' => $avatar,
                'total_reviews' => 0,
                'total_comments' => 0,
                'total_likes' => 0,
                'created_at' => date('c')
            ];
            
            $result = $this->request('POST', 'users', $data);
            return ['success' => true, 'data' => $result[0] ?? $result];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get user by ID
     */
    public function getUser($userId) {
        try {
            $result = $this->request('GET', "users?id=eq.$userId&select=*");
            
            if (empty($result)) {
                return ['success' => false, 'error' => 'User not found'];
            }
            
            return ['success' => true, 'data' => $result[0]];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update user profile
     */
    public function updateUser($userId, $data) {
        try {
            $result = $this->request('PATCH', "users?id=eq.$userId", $data);
            return ['success' => true, 'data' => $result[0] ?? $result];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get user by email
     */
    public function getUserByEmail($email) {
        try {
            $result = $this->request('GET', "users?email=eq." . urlencode($email) . "&select=*");
            
            if (empty($result)) {
                return ['success' => false, 'error' => 'User not found'];
            }
            
            return ['success' => true, 'data' => $result[0]];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ==================== REVIEW OPERATIONS ====================

    /**
     * Create new review
     */
    public function createReview($data) {
        try {
            $reviewData = [
                'user_id' => $data['userId'],
                'game_title' => $data['gameTitle'],
                'rating' => $data['rating'],
                'content' => $data['content'],
                'platform' => $data['platform'] ?? null,
                'likes_count' => 0,
                'comments_count' => 0,
                'created_at' => date('c')
            ];
            
            $result = $this->request('POST', 'reviews', $reviewData);
            
            // Increment user's review count
            $this->request('POST', 'rpc/increment_user_reviews', ['user_id' => $data['userId']]);
            
            return ['success' => true, 'data' => $result[0] ?? $result];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get all reviews with pagination
     */
    public function getReviews($limit = 50, $offset = 0) {
        try {
            $result = $this->request('GET', "reviews?select=*,users(display_name,avatar_url)&order=created_at.desc&limit=$limit&offset=$offset");
            return ['success' => true, 'data' => $result];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get review by ID
     */
    public function getReview($reviewId) {
        try {
            $result = $this->request('GET', "reviews?id=eq.$reviewId&select=*,users(display_name,avatar_url)");
            
            if (empty($result)) {
                return ['success' => false, 'error' => 'Review not found'];
            }
            
            return ['success' => true, 'data' => $result[0]];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get reviews by user
     */
    public function getUserReviews($userId) {
        try {
            $result = $this->request('GET', "reviews?user_id=eq.$userId&select=*&order=created_at.desc");
            return ['success' => true, 'data' => $result];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update review
     */
    public function updateReview($reviewId, $data) {
        try {
            $result = $this->request('PATCH', "reviews?id=eq.$reviewId", $data);
            return ['success' => true, 'data' => $result[0] ?? $result];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Delete review
     */
    public function deleteReview($reviewId) {
        try {
            $this->request('DELETE', "reviews?id=eq.$reviewId");
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ==================== COMMENT OPERATIONS ====================

    /**
     * Create comment
     */
    public function createComment($reviewId, $userId, $content) {
        try {
            $commentData = [
                'review_id' => $reviewId,
                'user_id' => $userId,
                'content' => $content,
                'created_at' => date('c')
            ];
            
            $result = $this->request('POST', 'comments', $commentData);
            
            // Increment comment counts
            $this->request('POST', 'rpc/increment_review_comments', ['review_id' => $reviewId]);
            $this->request('POST', 'rpc/increment_user_comments', ['user_id' => $userId]);
            
            return ['success' => true, 'data' => $result[0] ?? $result];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get comments for review
     */
    public function getReviewComments($reviewId) {
        try {
            $result = $this->request('GET', "comments?review_id=eq.$reviewId&select=*,users(display_name,avatar_url)&order=created_at.asc");
            return ['success' => true, 'data' => $result];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Delete comment
     */
    public function deleteComment($commentId) {
        try {
            $this->request('DELETE', "comments?id=eq.$commentId");
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ==================== LIKE OPERATIONS ====================

    /**
     * Add like to review
     */
    public function addLike($reviewId, $userId) {
        try {
            $likeData = [
                'review_id' => $reviewId,
                'user_id' => $userId,
                'created_at' => date('c')
            ];
            
            $result = $this->request('POST', 'likes', $likeData);
            
            // Increment like counts
            $this->request('POST', 'rpc/increment_review_likes', ['review_id' => $reviewId]);
            $this->request('POST', 'rpc/increment_user_likes', ['user_id' => $userId]);
            
            return ['success' => true, 'data' => $result[0] ?? $result];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Remove like from review
     */
    public function removeLike($reviewId, $userId) {
        try {
            $this->request('DELETE', "likes?review_id=eq.$reviewId&user_id=eq.$userId");
            
            // Decrement like counts
            $this->request('POST', 'rpc/decrement_review_likes', ['review_id' => $reviewId]);
            $this->request('POST', 'rpc/decrement_user_likes', ['user_id' => $userId]);
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check if user liked review
     */
    public function hasUserLiked($reviewId, $userId) {
        try {
            $result = $this->request('GET', "likes?review_id=eq.$reviewId&user_id=eq.$userId&select=id");
            return ['success' => true, 'data' => !empty($result)];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ==================== UTILITY METHODS ====================

    /**
     * Verify Supabase JWT token
     */
    public function verifyToken($token) {
        try {
            $ch = curl_init($this->supabaseUrl . '/auth/v1/user');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'apikey: ' . getenv('SUPABASE_ANON_KEY'),
                'Authorization: Bearer ' . $token
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                return ['success' => false, 'error' => 'Invalid token'];
            }
            
            $user = json_decode($response, true);
            return ['success' => true, 'data' => $user];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
