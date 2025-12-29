<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require __DIR__ . '/../../../db.php';
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
$product_id = $data['product_id'] ?? null;

if (!$product_id) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Product ID is required"]);
    exit;
}

// Delete related records first (bids, then auctions, then product)
// This ensures proper deletion even if foreign key constraints exist

// 1. Delete bids associated with auctions for this product
$delete_bids = $mysqli->prepare("DELETE b FROM bids b INNER JOIN auctions a ON b.auction_id = a.id WHERE a.product_id = ?");
if ($delete_bids) {
    $delete_bids->bind_param("i", $product_id);
    $delete_bids->execute();
    $delete_bids->close();
}

// 2. Delete auctions for this product
$delete_auctions = $mysqli->prepare("DELETE FROM auctions WHERE product_id = ?");
if ($delete_auctions) {
    $delete_auctions->bind_param("i", $product_id);
    $delete_auctions->execute();
    $delete_auctions->close();
}

// 3. Delete the product
$stmt = $mysqli->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        http_response_code(200);
        echo json_encode(["success" => true, "message" => "Product deleted successfully"]);
    } else {
        http_response_code(404);
        echo json_encode(["success" => false, "error" => "Product not found"]);
    }
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Failed to delete product: " . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>

