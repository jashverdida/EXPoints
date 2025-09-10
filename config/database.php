<?php
// Firebase/Firestore configuration and helper functions

class FirestoreDB {
    private $projectId = 'expoints-d6461';
    private $apiKey = 'AIzaSyAX8oYh-i_9Qe2RU8qNUidmx0OWrIJZPFY';
    private $baseUrl;
    
    public function __construct() {
        $this->baseUrl = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents";
    }
    
    /**
     * Create a new document in a collection
     */
    public function createDocument($collection, $documentId, $data) {
        $url = $this->baseUrl . "/{$collection}?documentId={$documentId}";
        
        $payload = [
            'fields' => $this->convertToFirestoreFields($data)
        ];
        
        return $this->makeRequest('POST', $url, $payload);
    }
    
    /**
     * Get a document from a collection
     */
    public function getDocument($collection, $documentId) {
        $url = $this->baseUrl . "/{$collection}/{$documentId}";
        return $this->makeRequest('GET', $url);
    }
    
    /**
     * Update a document in a collection
     */
    public function updateDocument($collection, $documentId, $data) {
        $url = $this->baseUrl . "/{$collection}/{$documentId}";
        
        $payload = [
            'fields' => $this->convertToFirestoreFields($data)
        ];
        
        return $this->makeRequest('PATCH', $url, $payload);
    }
    
    /**
     * Delete a document from a collection
     */
    public function deleteDocument($collection, $documentId) {
        $url = $this->baseUrl . "/{$collection}/{$documentId}";
        return $this->makeRequest('DELETE', $url);
    }
    
    /**
     * Query documents in a collection
     */
    public function queryCollection($collection, $orderBy = null, $limit = null) {
        $url = $this->baseUrl . "/{$collection}";
        
        if ($orderBy || $limit) {
            $params = [];
            if ($orderBy) $params['orderBy'] = $orderBy;
            if ($limit) $params['pageSize'] = $limit;
            $url .= '?' . http_build_query($params);
        }
        
        return $this->makeRequest('GET', $url);
    }
    
    /**
     * Convert PHP array to Firestore field format
     */
    private function convertToFirestoreFields($data) {
        $fields = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $fields[$key] = ['stringValue' => $value];
            } elseif (is_int($value)) {
                $fields[$key] = ['integerValue' => (string)$value];
            } elseif (is_float($value)) {
                $fields[$key] = ['doubleValue' => $value];
            } elseif (is_bool($value)) {
                $fields[$key] = ['booleanValue' => $value];
            } elseif (is_array($value)) {
                $fields[$key] = ['arrayValue' => ['values' => array_map([$this, 'convertToFirestoreFields'], $value)]];
            }
        }
        return $fields;
    }
    
    /**
     * Convert Firestore fields back to PHP array
     */
    public function convertFromFirestoreFields($fields) {
        $data = [];
        foreach ($fields as $key => $field) {
            if (isset($field['stringValue'])) {
                $data[$key] = $field['stringValue'];
            } elseif (isset($field['integerValue'])) {
                $data[$key] = (int)$field['integerValue'];
            } elseif (isset($field['doubleValue'])) {
                $data[$key] = (float)$field['doubleValue'];
            } elseif (isset($field['booleanValue'])) {
                $data[$key] = $field['booleanValue'];
            }
        }
        return $data;
    }
    
    /**
     * Make HTTP request to Firestore API
     */
    private function makeRequest($method, $url, $data = null) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ]
        ]);
        
        if ($data && in_array($method, ['POST', 'PATCH', 'PUT'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return json_decode($response, true);
        } else {
            throw new Exception("HTTP Error: $httpCode - $response");
        }
    }
}

// Collections structure for EXPoints
class EXPointsDB {
    private $db;
    
    public function __construct() {
        $this->db = new FirestoreDB();
    }
    
    // User management
    public function createUser($userId, $userData) {
        $userData['xpPoints'] = 0;
        $userData['level'] = 1;
        $userData['role'] = 'user';
        $userData['createdAt'] = date('Y-m-d H:i:s');
        return $this->db->createDocument('users', $userId, $userData);
    }
    
    public function getUser($userId) {
        return $this->db->getDocument('users', $userId);
    }
    
    // Review management
    public function createReview($reviewId, $reviewData) {
        $reviewData['createdAt'] = date('Y-m-d H:i:s');
        $reviewData['starUps'] = 0;
        $reviewData['comments'] = 0;
        return $this->db->createDocument('reviews', $reviewId, $reviewData);
    }
    
    public function getReviews($limit = 20) {
        return $this->db->queryCollection('reviews', 'createdAt desc', $limit);
    }
    
    // Comment management
    public function createComment($commentId, $commentData) {
        $commentData['createdAt'] = date('Y-m-d H:i:s');
        $commentData['starUps'] = 0;
        return $this->db->createDocument('comments', $commentId, $commentData);
    }
    
    // StarUp system
    public function addStarUp($collection, $documentId, $userId) {
        // TODO: Implement starUp logic with user tracking
        return $this->db->updateDocument($collection, $documentId, ['starUps' => 1]);
    }
}
?>
