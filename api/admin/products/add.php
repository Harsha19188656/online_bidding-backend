<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require __DIR__ . '/../../db.php';
require __DIR__ . '/helper_auth.php';

if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit;
}

if (!$mysqli->select_db("onlinebidding")) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database selection failed"]);
    exit;
}

// Verify admin token
$token = getAuthToken();

if (!$token) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Authorization token required"]);
    exit;
}

$user = verifyAdminToken($mysqli, $token);

if (!$user) {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Admin access required"]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid JSON data"]);
    exit;
}

$title = $data['title'] ?? '';
$description = $data['description'] ?? '';
$category = $data['category'] ?? '';
$image_url = $data['image_url'] ?? null;
$specs = $data['specs'] ?? null;
$condition_label = $data['condition_label'] ?? null;
$base_price = $data['base_price'] ?? 0;
$start_price = $data['start_price'] ?? $base_price;

if (!$title || !$category || !$base_price) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Title, category, and base_price are required"]);
    exit;
}

// Validate category
$valid_categories = ['laptop', 'mobile', 'computer', 'monitor', 'tablet'];
if (!in_array(strtolower($category), $valid_categories)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid category. Must be: " . implode(', ', $valid_categories)]);
    exit;
}

// Insert product
$specs_json = $specs ? json_encode($specs) : null;
$stmt = $mysqli->prepare("INSERT INTO products (title, description, category, image_url, specs, condition_label, base_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssd", $title, $description, $category, $image_url, $specs_json, $condition_label, $base_price);

if ($stmt->execute()) {
    $product_id = $mysqli->insert_id;
    
    // Create auction for this product
    $auction_stmt = $mysqli->prepare("INSERT INTO auctions (product_id, start_price, current_price, status) VALUES (?, ?, ?, 'scheduled')");
    $auction_stmt->bind_param("idd", $product_id, $start_price, $base_price);
    
    if ($auction_stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Product and auction created successfully",
            "product_id" => $product_id,
            "auction_id" => $mysqli->insert_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Product created but auction creation failed: " . $auction_stmt->error]);
    }
    $auction_stmt->close();
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Failed to create product: " . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>

