<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require __DIR__ . '/../../db.php';

$category = $_GET['category'] ?? null;
$search = $_GET['search'] ?? null;
$limit = intval($_GET['limit'] ?? 20);
$offset = intval($_GET['offset'] ?? 0);

$sql = "SELECT a.id as auction_id, a.product_id, a.start_price, a.current_price, a.status, a.start_at, a.end_at,
               p.id, p.title, p.description, p.category, p.image_url, p.specs, p.condition_label, p.base_price
        FROM auctions a
        JOIN products p ON p.id = a.product_id
        WHERE 1=1";

$params = [];
$types = "";

if ($category) {
    $sql .= " AND p.category=?";
    $types .= "s";
    $params[] = $category;
}

if ($search) {
    $sql .= " AND p.title LIKE ?";
    $types .= "s";
    $params[] = "%" . $search . "%";
}

$sql .= " ORDER BY a.created_at DESC LIMIT ? OFFSET ?";
$types .= "ii";
$params[] = $limit;
$params[] = $offset;

$stmt = $mysqli->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    // Parse specs JSON if it exists
    $specs = null;
    if ($row["specs"]) {
        $specsData = json_decode($row["specs"], true);
        if ($specsData) {
            $specsArray = [];
            foreach ($specsData as $key => $value) {
                $specsArray[] = "$key: $value";
            }
            $specs = implode(" · ", $specsArray);
        }
    }
    
    // Format price with ₹ symbol
    $price = "₹" . number_format($row["current_price"], 0);
    
    $items[] = [
        "product" => [
            "id" => intval($row["id"]),
            "title" => $row["title"],
            "description" => $row["description"],
            "category" => $row["category"],
            "image_url" => $row["image_url"],
            "specs" => $specs ?: $row["condition_label"] ?: "Premium Device",
            "condition" => $row["condition_label"],
            "base_price" => floatval($row["base_price"]),
            "created_at" => null
        ],
        "auction" => [
            "id" => intval($row["auction_id"]),
            "product_id" => intval($row["product_id"]),
            "start_price" => floatval($row["start_price"]),
            "current_price" => floatval($row["current_price"]),
            "status" => $row["status"],
            "start_at" => $row["start_at"],
            "end_at" => $row["end_at"]
        ],
        // Additional fields for LaptopList.kt compatibility
        "name" => $row["title"],
        "specs" => $specs ?: $row["condition_label"] ?: "Premium Device",
        "rating" => 4.5, // Default rating, can be added to products table later
        "price" => $price,
        "image_url" => $row["image_url"]
    ];
}

echo json_encode([
    "success" => true,
    "items" => $items,
    "count" => count($items)
]);
?>

