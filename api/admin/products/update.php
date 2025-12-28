<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PUT, OPTIONS');
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

if (!$data || !isset($data['product_id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Product ID is required"]);
    exit;
}

$product_id = $data['product_id'];
$title = $data['title'] ?? null;
$description = $data['description'] ?? null;
$category = $data['category'] ?? null;
$image_url = $data['image_url'] ?? null;
$specs = $data['specs'] ?? null;
$condition_label = $data['condition_label'] ?? null;
$base_price = $data['base_price'] ?? null;

// Build update query dynamically
$updates = [];
$params = [];
$types = "";

if ($title !== null) {
    $updates[] = "title = ?";
    $params[] = $title;
    $types .= "s";
}

if ($description !== null) {
    $updates[] = "description = ?";
    $params[] = $description;
    $types .= "s";
}

if ($category !== null) {
    $valid_categories = ['laptop', 'mobile', 'computer', 'monitor', 'tablet'];
    if (!in_array(strtolower($category), $valid_categories)) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Invalid category"]);
        exit;
    }
    $updates[] = "category = ?";
    $params[] = $category;
    $types .= "s";
}

if ($image_url !== null) {
    $updates[] = "image_url = ?";
    $params[] = $image_url;
    $types .= "s";
}

if ($specs !== null) {
    $updates[] = "specs = ?";
    $params[] = json_encode($specs);
    $types .= "s";
}

if ($condition_label !== null) {
    $updates[] = "condition_label = ?";
    $params[] = $condition_label;
    $types .= "s";
}

if ($base_price !== null) {
    $updates[] = "base_price = ?";
    $params[] = $base_price;
    $types .= "d";
}

if (empty($updates)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "No fields to update"]);
    exit;
}

$params[] = $product_id;
$types .= "i";

$sql = "UPDATE products SET " . implode(", ", $updates) . " WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(["success" => true, "message" => "Product updated successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Failed to update product: " . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>

