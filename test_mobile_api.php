<?php
/**
 * Quick Test: Mobile API
 * URL: http://localhost/onlinebidding/test_mobile_api.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require __DIR__ . '/db.php';

// Test database connection
if ($mysqli->connect_errno) {
    echo json_encode(["error" => "Database connection failed: " . $mysqli->connect_error]);
    exit;
}

// Check if mobiles exist in database
$result = $mysqli->query("SELECT COUNT(*) as count FROM products WHERE category = 'mobile'");
$row = $result->fetch_assoc();
$mobile_count = $row['count'];

echo json_encode([
    "database_status" => "connected",
    "mobile_count_in_db" => $mobile_count,
    "message" => $mobile_count > 0 ? "Mobiles found in database" : "NO MOBILES IN DATABASE - Run setup_complete.php"
]);

// Show actual mobile data
if ($mobile_count > 0) {
    echo "\n\n=== MOBILE DATA ===\n";
    $mobiles = $mysqli->query("SELECT id, title, category, base_price FROM products WHERE category = 'mobile'");
    while ($mobile = $mobiles->fetch_assoc()) {
        echo "ID: {$mobile['id']}, Title: {$mobile['title']}, Price: {$mobile['base_price']}\n";
    }
}

$mysqli->close();
?>

