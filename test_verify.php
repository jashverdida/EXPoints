<?php
// Test script to debug verify_user.php issues
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>EXPoints - Debug verify_user.php</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
        .success { color: #28a745; } .error { color: #dc3545; } .info { color: #17a2b8; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 10px 0; }
        pre { background: #e9ecef; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>

<h1>🔍 EXPoints Verify User Debug</h1>

<?php
echo '<div class="info"><h2>🔧 System Check</h2></div>';

// Check PHP version
echo '<p><strong>PHP Version:</strong> ' . phpversion() . '</p>';

// Check if required files exist
$requiredFiles = [
    'config/firestore.php' => 'Firestore Service Configuration',
    'config/firebase-service-account.json' => 'Firebase Service Account Key',
    'config/firebase-service-account.json.example' => 'Firebase Service Account Example'
];

foreach ($requiredFiles as $file => $description) {
    if (file_exists($file)) {
        echo '<p class="success">✅ ' . $description . ': <code>' . $file . '</code></p>';
    } else {
        echo '<p class="error">❌ ' . $description . ': <code>' . $file . '</code> - NOT FOUND</p>';
    }
}

// Check Firebase service account configuration
if (!file_exists('config/firebase-service-account.json')) {
    echo '<div class="error">';
    echo '<h3>❌ Firebase Service Account Missing</h3>';
    echo '<p><strong>Required:</strong> You need to create <code>config/firebase-service-account.json</code></p>';
    echo '<p><strong>Steps to fix:</strong></p>';
    echo '<ol>';
    echo '<li>Go to Firebase Console → Project Settings → Service Accounts</li>';
    echo '<li>Click "Generate new private key"</li>';
    echo '<li>Download the JSON file</li>';
    echo '<li>Rename it to <code>firebase-service-account.json</code></li>';
    echo '<li>Place it in the <code>config/</code> directory</li>';
    echo '</ol>';
    echo '</div>';
}

// Test basic connectivity
echo '<div class="info"><h2>🔗 Connectivity Test</h2></div>';

try {
    if (file_exists('config/firestore.php')) {
        require_once 'config/firestore.php';
        echo '<p class="success">✅ Firestore configuration loaded successfully</p>';
        
        if (file_exists('config/firebase-service-account.json')) {
            try {
                $firestoreService = new FirestoreService();
                echo '<p class="success">✅ FirestoreService initialized successfully</p>';
                
                // Test a simple operation
                echo '<p class="info">Testing Firestore connection...</p>';
                
            } catch (Exception $e) {
                echo '<p class="error">❌ FirestoreService initialization failed: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
        } else {
            echo '<p class="error">❌ Cannot test FirestoreService - service account key missing</p>';
        }
    } else {
        echo '<p class="error">❌ Firestore configuration file not found</p>';
    }
} catch (Exception $e) {
    echo '<p class="error">❌ Error loading configuration: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

// Test JSON handling
echo '<div class="info"><h2>📝 JSON Test</h2></div>';

$testData = [
    'idToken' => 'test-token-123',
    'email' => 'test@example.com',
    'uid' => 'test-uid-456'
];

$jsonString = json_encode($testData);
echo '<p><strong>Test JSON:</strong></p>';
echo '<pre>' . htmlspecialchars($jsonString) . '</pre>';

$decoded = json_decode($jsonString, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo '<p class="success">✅ JSON encode/decode working properly</p>';
} else {
    echo '<p class="error">❌ JSON error: ' . json_last_error_msg() . '</p>';
}

echo '<hr>';
echo '<h2>🚀 Next Steps</h2>';
echo '<ol>';
echo '<li><strong>Configure Firebase Service Account:</strong> Download and place the JSON key file</li>';
echo '<li><strong>Test Login:</strong> Try logging in after configuration</li>';
echo '<li><strong>Check Browser Console:</strong> Look for detailed error messages</li>';
echo '</ol>';

echo '<p><a href="login.php">← Back to Login</a> | <a href="dashboard.php">Dashboard →</a></p>';
?>

</body>
</html>
