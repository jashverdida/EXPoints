<?php
/**
 * Quick test to verify login functionality
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Test</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        h1 { color: #333; margin-top: 0; }
        .success { background: #4CAF50; color: white; padding: 15px; margin: 10px 0; border-radius: 8px; }
        .error { background: #f44336; color: white; padding: 15px; margin: 10px 0; border-radius: 8px; }
        .info { background: #2196F3; color: white; padding: 15px; margin: 10px 0; border-radius: 8px; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .test-section { margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Login Functionality Test</h1>
        
        <?php
        // Test 1: Environment check
        echo '<div class="test-section">';
        echo '<h2>Test 1: Environment & Database</h2>';
        try {
            require_once __DIR__ . '/includes/db_helper.php';
            $db = getDBConnection();
            echo '<p class="success">✅ Database connection successful</p>';
        } catch (Exception $e) {
            echo '<p class="error">❌ Database connection failed: ' . $e->getMessage() . '</p>';
            echo '<p>Check your .env file configuration.</p>';
        }
        echo '</div>';
        
        // Test 2: Check if user exists
        if (isset($db) && $db) {
            echo '<div class="test-section">';
            echo '<h2>Test 2: Check User Exists</h2>';
            
            $testEmail = 'eijay.pepito8@gmail.com';
            
            try {
                $stmt = $db->prepare("SELECT id, email, password, role FROM users WHERE email = ?");
                $stmt->bind_param("s", $testEmail);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result && $result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    echo '<p class="success">✅ User found in database</p>';
                    echo '<pre>';
                    echo 'Email: ' . htmlspecialchars($user['email']) . "\n";
                    echo 'User ID: ' . $user['id'] . "\n";
                    echo 'Role: ' . ($user['role'] ?? 'not set') . "\n";
                    echo 'Password (first 10 chars): ' . substr($user['password'], 0, 10) . '...' . "\n";
                    echo '</pre>';
                    
                    // Test 3: Check user_info
                    echo '<h3>User Info Table</h3>';
                    $userInfoStmt = $db->prepare("SELECT username, is_banned FROM user_info WHERE user_id = ?");
                    $userInfoStmt->bind_param("i", $user['id']);
                    $userInfoStmt->execute();
                    $userInfoResult = $userInfoStmt->get_result();
                    
                    if ($userInfoResult && $userInfoResult->num_rows > 0) {
                        $userInfo = $userInfoResult->fetch_assoc();
                        echo '<p class="success">✅ User info found</p>';
                        echo '<pre>';
                        echo 'Username: ' . htmlspecialchars($userInfo['username']) . "\n";
                        echo 'Is Banned: ' . ($userInfo['is_banned'] ? 'Yes' : 'No') . "\n";
                        echo '</pre>';
                    } else {
                        echo '<p class="error">⚠️ User info not found (username might be missing)</p>';
                    }
                    
                    $userInfoStmt->close();
                } else {
                    echo '<p class="error">❌ User not found in database</p>';
                    echo '<p>Email searched: <code>' . htmlspecialchars($testEmail) . '</code></p>';
                    echo '<p>Make sure this user exists in your Supabase "users" table.</p>';
                }
                
                $stmt->close();
            } catch (Exception $e) {
                echo '<p class="error">❌ Query error: ' . $e->getMessage() . '</p>';
                echo '<pre>' . $e->getTraceAsString() . '</pre>';
            }
            echo '</div>';
            
            // Test 3: Parse WHERE clause test
            echo '<div class="test-section">';
            echo '<h2>Test 3: WHERE Clause Parsing</h2>';
            echo '<p>Testing if email can be properly parsed in WHERE clause...</p>';
            
            $testWhere = "email = 'eijay.pepito8@gmail.com'";
            echo '<p><strong>Test WHERE:</strong> <code>' . $testWhere . '</code></p>';
            
            // Simulate what parseWhereClause does
            if (preg_match('/(\w+)\s*=\s*[\'"]([^\'"]+)[\'"]/', $testWhere, $matches)) {
                echo '<p class="success">✅ Regex matched!</p>';
                echo '<pre>';
                echo 'Column: ' . $matches[1] . "\n";
                echo 'Value: ' . $matches[2] . "\n";
                echo 'Supabase filter: ' . $matches[1] . '=eq.' . urlencode($matches[2]) . "\n";
                echo '</pre>';
            } else {
                echo '<p class="error">❌ Regex did not match</p>';
            }
            echo '</div>';
        }
        ?>
        
        <div class="test-section">
            <h2>Next Steps</h2>
            <ol>
                <li>If all tests above passed, try logging in: <a href="user/login.php">Go to Login Page</a></li>
                <li>If user not found, add the user to your Supabase "users" table</li>
                <li>If WHERE parsing failed, there's a regex issue (contact developer)</li>
            </ol>
        </div>
        
        <div class="info">
            <strong>💡 Test Account:</strong><br>
            Email: <code>eijay.pepito8@gmail.com</code><br>
            Password: <code>Eijay123.</code><br><br>
            Make sure this account exists in your Supabase database with this exact email and password.
        </div>
    </div>
</body>
</html>
