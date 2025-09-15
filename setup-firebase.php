<?php
// Firebase Setup and Configuration Helper
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Firebase Setup Helper</h1>";

// Check if service account file exists
$serviceAccountPath = 'config/firebase-service-account.json';
$examplePath = 'config/firebase-service-account.json.example';

if (file_exists($serviceAccountPath)) {
    echo "<p style='color: green;'>✅ Service account file exists</p>";
    
    // Test the configuration
    try {
        require_once 'config/firestore.php';
        $firestore = new FirestoreService();
        echo "<p style='color: green;'>✅ FirestoreService initialized successfully</p>";
        
        // Test a simple operation
        echo "<p>Testing Firestore connection...</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error initializing FirestoreService: " . $e->getMessage() . "</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Service account file NOT found</p>";
    echo "<p>You need to create: <code>$serviceAccountPath</code></p>";
    
    if (file_exists($examplePath)) {
        echo "<h3>Steps to set up Firebase:</h3>";
        echo "<ol>";
        echo "<li>Go to <a href='https://console.firebase.google.com' target='_blank'>Firebase Console</a></li>";
        echo "<li>Select your project: <strong>expoints-d6461</strong></li>";
        echo "<li>Go to Project Settings (gear icon) → Service Accounts</li>";
        echo "<li>Click 'Generate new private key'</li>";
        echo "<li>Download the JSON file</li>";
        echo "<li>Rename it to <code>firebase-service-account.json</code></li>";
        echo "<li>Place it in the <code>config/</code> folder</li>";
        echo "</ol>";
        
        echo "<h3>Example file structure:</h3>";
        echo "<pre>";
        echo htmlspecialchars(file_get_contents($examplePath));
        echo "</pre>";
    }
}

// Check composer dependencies
echo "<h3>Checking Dependencies:</h3>";
if (file_exists('vendor/autoload.php')) {
    echo "<p style='color: green;'>✅ Composer dependencies installed</p>";
} else {
    echo "<p style='color: red;'>❌ Composer dependencies not installed</p>";
    echo "<p>Run: <code>composer install</code></p>";
}

// Check PHP version
echo "<h3>PHP Environment:</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Required: ^7.4|^8.0</p>";

if (version_compare(phpversion(), '7.4.0', '>=')) {
    echo "<p style='color: green;'>✅ PHP version is compatible</p>";
} else {
    echo "<p style='color: red;'>❌ PHP version is too old</p>";
}

// Check required extensions
$requiredExtensions = ['json', 'curl', 'openssl'];
echo "<h3>Required Extensions:</h3>";
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p style='color: green;'>✅ $ext extension loaded</p>";
    } else {
        echo "<p style='color: red;'>❌ $ext extension not loaded</p>";
    }
}

echo "<hr>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li>Set up the Firebase service account file</li>";
echo "<li>Test the registration process</li>";
echo "<li>Check Firestore console to see if users are created</li>";
echo "</ol>";
?>
