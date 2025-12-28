<?php
// Debug script for login
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require __DIR__ . '/db.php';

echo "Testing Login Debug...\n\n";

// Check database connection
if ($mysqli->connect_errno) {
    echo "Database connection failed: " . $mysqli->connect_error . "\n";
    exit;
}

echo "✓ Database connected\n";

// Check if database exists
if (!$mysqli->select_db("onlinebidding")) {
    echo "✗ Database 'onlinebidding' selection failed: " . $mysqli->error . "\n";
    exit;
}

echo "✓ Database 'onlinebidding' selected\n";

// Check if users table exists
$result = $mysqli->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows == 0) {
    echo "✗ Table 'users' does not exist\n";
    exit;
}

echo "✓ Table 'users' exists\n";

// Check columns in users table
$result = $mysqli->query("DESCRIBE users");
echo "\nColumns in users table:\n";
while ($row = $result->fetch_assoc()) {
    echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

// Check if is_admin column exists
$result = $mysqli->query("SHOW COLUMNS FROM users LIKE 'is_admin'");
if ($result->num_rows > 0) {
    echo "\n✓ Column 'is_admin' exists\n";
} else {
    echo "\n✗ Column 'is_admin' does NOT exist (this is OK, will default to 0)\n";
}

// Test query
echo "\nTesting SELECT query...\n";
$stmt = $mysqli->prepare("SELECT id, password_hash, name, phone FROM users LIMIT 1");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        echo "✓ SELECT query works\n";
        echo "  Sample user: " . $row['name'] . " (" . $row['id'] . ")\n";
    }
    $stmt->close();
} else {
    echo "✗ SELECT query failed: " . $mysqli->error . "\n";
}

// Test sessions table
$result = $mysqli->query("SHOW TABLES LIKE 'sessions'");
if ($result->num_rows == 0) {
    echo "\n✗ Table 'sessions' does not exist\n";
} else {
    echo "\n✓ Table 'sessions' exists\n";
}

echo "\nDone!\n";
?>

