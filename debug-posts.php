<!DOCTYPE html>
<html>
<head>
    <title>Debug Posts</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .test { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h2 { color: #333; }
        .error { color: red; }
        .success { color: green; }
        pre { background: #f9f9f9; padding: 15px; border-radius: 4px; overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>🔍 Dashboard Posts Debug Tool</h1>
    
    <div class="test">
        <h2>1. Session Check</h2>
        <?php
        session_start();
        if (isset($_SESSION['user_id'])) {
            echo "<p class='success'>✅ Session Active</p>";
            echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
            echo "<p>Email: " . ($_SESSION['user_email'] ?? 'Not set') . "</p>";
            echo "<p>Username: " . ($_SESSION['username'] ?? 'Not set') . "</p>";
        } else {
            echo "<p class='error'>❌ No Session - You must be logged in!</p>";
            echo "<p><a href='user/login.php'>Go to Login</a></p>";
            exit;
        }
        ?>
    </div>

    <div class="test">
        <h2>2. Database Connection</h2>
        <?php
        $host = '127.0.0.1';
        $dbname = 'expoints_db';
        $username = 'root';
        $password = '';
        
        try {
            $mysqli = new mysqli($host, $username, $password, $dbname);
            if ($mysqli->connect_error) {
                throw new Exception($mysqli->connect_error);
            }
            echo "<p class='success'>✅ Database Connected</p>";
        } catch (Exception $e) {
            echo "<p class='error'>❌ Database Error: " . $e->getMessage() . "</p>";
            exit;
        }
        ?>
    </div>

    <div class="test">
        <h2>3. Posts Table Structure</h2>
        <?php
        $result = $mysqli->query("DESCRIBE posts");
        if ($result) {
            echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['Field'] . "</td>";
                echo "<td>" . $row['Type'] . "</td>";
                echo "<td>" . $row['Null'] . "</td>";
                echo "<td>" . $row['Key'] . "</td>";
                echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        ?>
    </div>

    <div class="test">
        <h2>4. All Posts in Database</h2>
        <?php
        $result = $mysqli->query("SELECT * FROM posts ORDER BY created_at DESC");
        if ($result && $result->num_rows > 0) {
            echo "<p class='success'>✅ Found " . $result->num_rows . " post(s)</p>";
            echo "<table><tr><th>ID</th><th>User ID</th><th>Username</th><th>Game</th><th>Title</th><th>Content (preview)</th><th>Created</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . ($row['user_id'] ?? '<span class="error">NULL</span>') . "</td>";
                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                echo "<td>" . htmlspecialchars($row['game']) . "</td>";
                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                echo "<td>" . htmlspecialchars(substr($row['content'], 0, 50)) . "...</td>";
                echo "<td>" . $row['created_at'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='error'>❌ No posts found in database</p>";
        }
        ?>
    </div>

    <div class="test">
        <h2>5. API Simulation (get_posts)</h2>
        <?php
        $userId = $_SESSION['user_id'];
        $stmt = $mysqli->prepare("
            SELECT 
                p.id,
                p.game,
                p.title,
                p.content,
                p.username,
                p.user_id,
                p.created_at,
                (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as like_count,
                (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) as comment_count,
                (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id AND user_id = ?) as user_liked
            FROM posts p
            ORDER BY p.created_at DESC
        ");
        
        if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $posts = [];
            while ($row = $result->fetch_assoc()) {
                $row['user_liked'] = (bool)$row['user_liked'];
                $row['is_owner'] = ($row['user_id'] === $userId);
                $posts[] = $row;
            }
            
            echo "<p class='success'>✅ API would return " . count($posts) . " post(s)</p>";
            echo "<pre>" . json_encode(['success' => true, 'posts' => $posts], JSON_PRETTY_PRINT) . "</pre>";
            
            $stmt->close();
        } else {
            echo "<p class='error'>❌ Query failed: " . $mysqli->error . "</p>";
        }
        ?>
    </div>

    <div class="test">
        <h2>6. JavaScript File Check</h2>
        <?php
        $jsFile = 'assets/js/dashboard-posts.js';
        if (file_exists($jsFile)) {
            $fileSize = filesize($jsFile);
            $lastModified = date("Y-m-d H:i:s", filemtime($jsFile));
            echo "<p class='success'>✅ JavaScript file exists</p>";
            echo "<p>File: $jsFile</p>";
            echo "<p>Size: " . number_format($fileSize) . " bytes</p>";
            echo "<p>Last Modified: $lastModified</p>";
            
            // Check for syntax errors (basic)
            $content = file_get_contents($jsFile);
            $hasLoadPosts = strpos($content, 'function loadPosts()') !== false;
            $hasRenderPosts = strpos($content, 'function renderPosts(posts)') !== false;
            $hasDOMContentLoaded = strpos($content, 'DOMContentLoaded') !== false;
            
            echo "<p>" . ($hasDOMContentLoaded ? "✅" : "❌") . " Has DOMContentLoaded listener</p>";
            echo "<p>" . ($hasLoadPosts ? "✅" : "❌") . " Has loadPosts() function</p>";
            echo "<p>" . ($hasRenderPosts ? "✅" : "❌") . " Has renderPosts() function</p>";
        } else {
            echo "<p class='error'>❌ JavaScript file not found!</p>";
        }
        ?>
    </div>

    <div class="test">
        <h2>7. API Direct Test</h2>
        <p>Click to test API directly:</p>
        <button onclick="testAPI()">Test get_posts API</button>
        <pre id="apiResult">Waiting for test...</pre>
    </div>

    <script>
    function testAPI() {
        const resultDiv = document.getElementById('apiResult');
        resultDiv.textContent = 'Loading...';
        
        fetch('api/posts.php?action=get_posts')
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('API Response:', data);
                resultDiv.textContent = JSON.stringify(data, null, 2);
                if (data.success) {
                    resultDiv.style.color = 'green';
                } else {
                    resultDiv.style.color = 'red';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                resultDiv.textContent = 'Error: ' + error.message;
                resultDiv.style.color = 'red';
            });
    }
    </script>

    <?php $mysqli->close(); ?>
</body>
</html>
