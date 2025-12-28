<?php
// Test script to check authorization header handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

echo "Testing Authorization Header...\n\n";

// Method 1: HTTP_AUTHORIZATION
echo "HTTP_AUTHORIZATION: " . (isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : 'NOT SET') . "\n";

// Method 2: getallheaders
if (function_exists('getallheaders')) {
    $headers = getallheaders();
    echo "getallheaders() Available: YES\n";
    echo "Authorization from getallheaders: " . ($headers['Authorization'] ?? $headers['authorization'] ?? 'NOT SET') . "\n";
} else {
    echo "getallheaders() Available: NO\n";
}

// Method 3: Apache specific
if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    echo "REDIRECT_HTTP_AUTHORIZATION: " . $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] . "\n";
}

// Method 4: All headers
echo "\nAll headers:\n";
print_r(getallheaders());
?>

