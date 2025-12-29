<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
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

$category = $_GET['category'] ?? null;
$limit = intval($_GET['limit'] ?? 100);
$offset = intval($_GET['offset'] ?? 0);

$sql = "SELECT p.*, a.id as auction_id, a.start_price, a.current_price, a.status as auction_status 
        FROM products p 
        LEFT JOIN auctions a ON p.id = a.product_id";

if ($category) {
    $sql .= " WHERE p.category = ?";
}

$sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";

$stmt = $mysqli->prepare($sql);

if ($category) {
    $stmt->bind_param("sii", $category, $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $specs = null;
    if ($row['specs']) {
        $specs = json_decode($row['specs'], true);
    }
    
    $products[] = [
        "id" => intval($row['id']),
        "title" => $row['title'],
        "description" => $row['description'],
        "category" => $row['category'],
        "image_url" => $row['image_url'],
        "specs" => $specs,
        "condition_label" => $row['condition_label'],
        "base_price" => floatval($row['base_price']),
        "auction_id" => $row['auction_id'] ? intval($row['auction_id']) : null,
        "start_price" => $row['start_price'] ? floatval($row['start_price']) : null,
        "current_price" => $row['current_price'] ? floatval($row['current_price']) : null,
        "auction_status" => $row['auction_status'],
        "created_at" => $row['created_at']
    ];
}

$stmt->close();

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM products";
if ($category) {
    $count_sql .= " WHERE category = ?";
    $count_stmt = $mysqli->prepare($count_sql);
    $count_stmt->bind_param("s", $category);
} else {
    $count_stmt = $mysqli->prepare($count_sql);
}

$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total = $count_result->fetch_assoc()['total'];
$count_stmt->close();

http_response_code(200);
echo json_encode([
    "success" => true,
    "products" => $products,
    "total" => intval($total),
    "count" => count($products)
]);

$mysqli->close();
?>

