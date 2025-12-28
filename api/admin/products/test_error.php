<?php
// Test script to check for PHP errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing PHP configuration...\n\n";

// Test 1: Check if helper_auth.php can be included
echo "1. Testing helper_auth.php inclusion...\n";
try {
    require __DIR__ . '/helper_auth.php';
    echo "   ✓ helper_auth.php loaded successfully\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Test 2: Check database connection
echo "\n2. Testing database connection...\n";
require __DIR__ . '/../../db.php';
if ($mysqli->connect_errno) {
    echo "   ✗ Database connection failed: " . $mysqli->connect_error . "\n";
} else {
    echo "   ✓ Database connected\n";
    
    if ($mysqli->select_db("onlinebidding")) {
        echo "   ✓ Database 'onlinebidding' selected\n";
    } else {
        echo "   ✗ Database selection failed: " . $mysqli->error . "\n";
    }
}

// Test 3: Check if getallheaders exists
echo "\n3. Testing getallheaders()...\n";
if (function_exists('getallheaders')) {
    echo "   ✓ getallheaders() available\n";
    $headers = getallheaders();
    echo "   Headers: " . print_r($headers, true) . "\n";
} else {
    echo "   ✗ getallheaders() not available\n";
}

// Test 4: Check auth token function
echo "\n4. Testing getAuthToken()...\n";
if (function_exists('getAuthToken')) {
    echo "   ✓ getAuthToken() function exists\n";
    $token = getAuthToken();
    echo "   Token: " . ($token ?: 'NULL') . "\n";
} else {
    echo "   ✗ getAuthToken() function not found\n";
}

echo "\nDone!\n";
?>

