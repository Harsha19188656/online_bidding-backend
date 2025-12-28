<?php
// Test registration endpoint
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require __DIR__ . '/../db.php';

// Test data
$testData = [
    "name" => "Test User",
    "email" => "test@example.com",
    "phone" => "+91 9876543210",
    "dob" => "01/01/1990",
    "gender" => "Male",
    "password" => "test123"
];

echo json_encode([
    "test" => "Registration endpoint is accessible",
    "database_connected" => isset($mysqli) && !$mysqli->connect_errno,
    "test_data" => $testData,
    "message" => "If you see this, the endpoint is working. Try sending POST request with this data."
]);
?>

