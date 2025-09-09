<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class FirestoreService {
    private $firestore;
    private $auth;

    public function __construct() {
        try {
            // Use the Firebase Admin SDK (already installed)
            $serviceAccount = ServiceAccount::fromJsonFile(__DIR__ . '/firebase-service-account.json');
            $factory = (new Factory)
                ->withServiceAccount($serviceAccount);
                
            $this->firestore = $factory->createFirestore()->database();
            $this->auth = $factory->createAuth();
            
        } catch (Exception $e) {
            throw new Exception('Firebase Admin SDK initialization failed: ' . $e->getMessage());
        }
    }

    public function createUser($uid, $userData) {
        try {
            // Hash password if provided
            if (isset($userData['password'])) {
                $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
            }
            
            // Add timestamps
            $userData['createdAt'] = new DateTime();
            $userData['updatedAt'] = new DateTime();
            $userData['stats'] = [
                'totalComments' => 0,
                'totalLikes' => 0,
                'totalReviews' => 0
            ];
            
            // Create user document in Firestore using Admin SDK
            $userRef = $this->firestore->collection('users')->document($uid);
            $userRef->set($userData);
            
            return ['success' => true, 'uid' => $uid, 'method' => 'firebase-admin-sdk'];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public function getUser($uid) {
        try {
            $userRef = $this->firestore->collection('users')->document($uid);
            $snapshot = $userRef->snapshot();
            
            if ($snapshot->exists()) {
                $userData = $snapshot->data();
                $userData['uid'] = $uid;
                return ['success' => true, 'data' => $userData];
            } else {
                return ['success' => false, 'error' => 'User not found'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getUserByEmail($email) {
        try {
            $usersRef = $this->firestore->collection('users');
            $query = $usersRef->where('email', '=', $email)->limit(1);
            $documents = $query->documents();
            
            foreach ($documents as $document) {
                if ($document->exists()) {
                    $userData = $document->data();
                    $userData['uid'] = $document->id();
                    return ['success' => true, 'user' => $userData];
                }
            }
            
            return ['success' => false, 'error' => 'User not found'];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function createReview($reviewData) {
        try {
            $reviewData['createdAt'] = new DateTime();
            $reviewData['updatedAt'] = new DateTime();
            $reviewData['likes'] = 0;
            
            $reviewRef = $this->firestore->collection('reviews')->newDocument();
            $reviewRef->set($reviewData);
            
            // Update user stats
            if (isset($reviewData['userId'])) {
                $this->updateUserStats($reviewData['userId'], 'review_created');
            }
            
            return ['success' => true, 'reviewId' => $reviewRef->id()];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getReviews($limit = 10) {
        try {
            $reviewsRef = $this->firestore->collection('reviews');
            $query = $reviewsRef->orderBy('createdAt', 'DESC')->limit($limit);
            $documents = $query->documents();
            
            $reviews = [];
            foreach ($documents as $document) {
                if ($document->exists()) {
                    $reviewData = $document->data();
                    $reviewData['id'] = $document->id();
                    
                    // Get user info for review
                    if (isset($reviewData['userId'])) {
                        $userResult = $this->getUser($reviewData['userId']);
                        if ($userResult['success']) {
                            $reviewData['user'] = $userResult['data'];
                            unset($reviewData['user']['password']);
                        }
                    }
                    
                    $reviews[] = $reviewData;
                }
            }
            
            return ['success' => true, 'data' => $reviews];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function addComment($reviewId, $userId, $content) {
        try {
            $commentData = [
                'reviewId' => $reviewId,
                'userId' => $userId,
                'content' => $content,
                'createdAt' => new DateTime()
            ];
            
            $commentRef = $this->firestore->collection('comments')->newDocument();
            $commentRef->set($commentData);
            
            // Update user stats
            $this->updateUserStats($userId, 'comment_added');
            
            return ['success' => true, 'commentId' => $commentRef->id()];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getComments($reviewId) {
        try {
            $commentsRef = $this->firestore->collection('comments');
            $query = $commentsRef->where('reviewId', '=', $reviewId)->orderBy('createdAt', 'ASC');
            $documents = $query->documents();
            
            $comments = [];
            foreach ($documents as $document) {
                if ($document->exists()) {
                    $commentData = $document->data();
                    $commentData['id'] = $document->id();
                    
                    // Get user info for comment
                    if (isset($commentData['userId'])) {
                        $userResult = $this->getUser($commentData['userId']);
                        if ($userResult['success']) {
                            $commentData['user'] = $userResult['data'];
                            unset($commentData['user']['password']);
                        }
                    }
                    
                    $comments[] = $commentData;
                }
            }
            
            return ['success' => true, 'data' => $comments];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateUserStats($userId, $action) {
        try {
            $userRef = $this->firestore->collection('users')->document($userId);
            $snapshot = $userRef->snapshot();
            
            if ($snapshot->exists()) {
                $userData = $snapshot->data();
                $stats = $userData['stats'] ?? [
                    'totalComments' => 0,
                    'totalLikes' => 0,
                    'totalReviews' => 0
                ];
                
                switch ($action) {
                    case 'review_created':
                        $stats['totalReviews'] = ($stats['totalReviews'] ?? 0) + 1;
                        break;
                    case 'comment_added':
                        $stats['totalComments'] = ($stats['totalComments'] ?? 0) + 1;
                        break;
                    case 'like_given':
                        $stats['totalLikes'] = ($stats['totalLikes'] ?? 0) + 1;
                        break;
                }
                
                $userRef->update([
                    'stats' => $stats,
                    'updatedAt' => new DateTime()
                ]);
                
                return ['success' => true];
            }
            
            return ['success' => false, 'error' => 'User not found'];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function likeReview($reviewId, $userId) {
        try {
            // Create like record
            $likeData = [
                'reviewId' => $reviewId,
                'userId' => $userId,
                'createdAt' => new DateTime()
            ];
            
            $likeRef = $this->firestore->collection('likes')->newDocument();
            $likeRef->set($likeData);
            
            // Update user stats
            $this->updateUserStats($userId, 'like_given');
            
            // Update review like count
            $this->incrementReviewLikes($reviewId);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function incrementReviewLikes($reviewId) {
        try {
            $reviewRef = $this->firestore->collection('reviews')->document($reviewId);
            $snapshot = $reviewRef->snapshot();
            
            if ($snapshot->exists()) {
                $reviewData = $snapshot->data();
                $likes = ($reviewData['likes'] ?? 0) + 1;
                
                $reviewRef->update([
                    'likes' => $likes,
                    'updatedAt' => new DateTime()
                ]);
                
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            return false;
        }
    }

    public function authenticateUser($email, $password) {
        // This method should use Firebase Auth, not direct Firestore
        return ['success' => false, 'error' => 'Use Firebase Authentication for login'];
    }
}
?>
