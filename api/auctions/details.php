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

$auction_id = intval($_GET['id'] ?? 0);

if ($auction_id <= 0) {
    echo json_encode([
        "success" => false,
        "error" => "Invalid auction ID"
    ]);
    exit;
}

// Fetch auction and product details
$sql = "SELECT a.id as auction_id, a.product_id, a.start_price, a.current_price, a.status, 
               a.start_at, a.end_at, a.created_at,
               p.id, p.title, p.description, p.category, p.image_url, p.specs, 
               p.condition_label, p.base_price, p.created_at as product_created_at
        FROM auctions a
        JOIN products p ON p.id = a.product_id
        WHERE a.id = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $auction_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "error" => "Auction not found"
    ]);
    exit;
}

$row = $result->fetch_assoc();

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

$product = [
    "id" => intval($row["id"]),
    "title" => $row["title"],
    "description" => $row["description"],
    "category" => $row["category"],
    "image_url" => $row["image_url"],
    "specs" => $specs ?: $row["condition_label"] ?: "Premium Device",
    "condition" => $row["condition_label"],
    "base_price" => floatval($row["base_price"]),
    "created_at" => $row["product_created_at"]
];

$auction = [
    "id" => intval($row["auction_id"]),
    "product_id" => intval($row["product_id"]),
    "start_price" => floatval($row["start_price"]),
    "current_price" => floatval($row["current_price"]),
    "status" => $row["status"],
    "start_at" => $row["start_at"],
    "end_at" => $row["end_at"]
];

// Fetch bids for this auction
$bidsSql = "SELECT b.id, b.auction_id, b.user_id, b.amount, b.created_at,
                   u.name as user_name
            FROM bids b
            LEFT JOIN users u ON u.id = b.user_id
            WHERE b.auction_id = ?
            ORDER BY b.amount DESC, b.created_at DESC
            LIMIT 50";

$bidsStmt = $mysqli->prepare($bidsSql);
$bidsStmt->bind_param("i", $auction_id);
$bidsStmt->execute();
$bidsResult = $bidsStmt->get_result();

$bids = [];
while ($bidRow = $bidsResult->fetch_assoc()) {
    $bids[] = [
        "id" => intval($bidRow["id"]),
        "auction_id" => intval($bidRow["auction_id"]),
        "user_id" => intval($bidRow["user_id"]),
        "amount" => floatval($bidRow["amount"]),
        "created_at" => $bidRow["created_at"],
        "user_name" => $bidRow["user_name"] ?? "Anonymous"
    ];
}

echo json_encode([
    "success" => true,
    "product" => $product,
    "auction" => $auction,
    "bids" => $bids
]);
?>

