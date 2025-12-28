<?php
/**
 * Test Login API
 * URL: http://localhost/onlinebidding/test_login_api.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Login API</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #1a1a1a; color: #fff; }
        .success { color: #4CAF50; padding: 10px; background: #2a2a2a; margin: 10px 0; border-left: 4px solid #4CAF50; }
        .error { color: #f44336; padding: 10px; background: #2a2a2a; margin: 10px 0; border-left: 4px solid #f44336; }
        .info { color: #2196F3; padding: 10px; background: #2a2a2a; margin: 10px 0; border-left: 4px solid #2196F3; }
        h1 { color: #FFC107; }
        pre { background: #2a2a2a; padding: 15px; border-radius: 5px; overflow-x: auto; color: #4CAF50; }
    </style>
</head>
<body>
    <h1>🧪 Test Login API</h1>
    
<?php

// Test database connection
require __DIR__ . '/db.php';

if ($mysqli->connect_errno) {
    echo "<div class='error'>❌ Database connection failed: " . $mysqli->connect_error . "</div>";
    exit;
}
echo "<div class='success'>✅ Database connection successful</div>";

// Test database selection
if (!$mysqli->select_db("onlinebidding")) {
    echo "<div class='error'>❌ Cannot select database 'onlinebidding'</div>";
    exit;
}
echo "<div class='success'>✅ Database 'onlinebidding' selected</div>";

// Check if users table exists
$result = $mysqli->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows == 0) {
    echo "<div class='error'>❌ Table 'users' does not exist! Run setup_complete.php</div>";
    exit;
}
echo "<div class='success'>✅ Table 'users' exists</div>";

// Check if sessions table exists
$result = $mysqli->query("SHOW TABLES LIKE 'sessions'");
if ($result->num_rows == 0) {
    echo "<div class='error'>❌ Table 'sessions' does not exist! Run setup_complete.php</div>";
    exit;
}
echo "<div class='success'>✅ Table 'sessions' exists</div>";

// Check if there are any users
$result = $mysqli->query("SELECT COUNT(*) as count FROM users");
$row = $result->fetch_assoc();
echo "<div class='info'>📊 Total users in database: {$row['count']}</div>";

if ($row['count'] > 0) {
    // Show sample users (without passwords)
    $result = $mysqli->query("SELECT id, email, name FROM users LIMIT 5");
    echo "<div class='info'><strong>Sample Users:</strong></div>";
    while ($user = $result->fetch_assoc()) {
        echo "<div class='info'>- ID: {$user['id']}, Email: {$user['email']}, Name: {$user['name']}</div>";
    }
} else {
    echo "<div class='error'>⚠️ No users found! Register a user first.</div>";
}

$mysqli->close();

?>

    <div class='info'>
        <h3>🔗 Test Login API</h3>
        <p>Use a tool like Postman or cURL to test:</p>
        <pre>
POST http://localhost/onlinebidding/api/login.php
Content-Type: application/json

{
  "email": "test@example.com",
  "password": "test1234"
}
        </pre>
    </div>

</body>
</html>

