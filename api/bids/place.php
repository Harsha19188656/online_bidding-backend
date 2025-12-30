<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require __DIR__ . '/../../db.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode([
        "success" => false,
        "error" => "Invalid JSON input"
    ]);
    exit;
}

$auction_id = intval($input['auction_id'] ?? 0);
$amount = floatval($input['amount'] ?? 0);
$user_id = intval($input['user_id'] ?? 1); // Default to user_id 1 if not provided

if ($auction_id <= 0 || $amount <= 0) {
    echo json_encode([
        "success" => false,
        "error" => "Invalid auction_id or amount"
    ]);
    exit;
}

// Check if auction exists and is active
$checkSql = "SELECT id, current_price, status, end_at FROM auctions WHERE id = ?";
$checkStmt = $mysqli->prepare($checkSql);
$checkStmt->bind_param("i", $auction_id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "error" => "Auction not found"
    ]);
    exit;
}

$auction = $checkResult->fetch_assoc();

// Check if auction is still active
if ($auction['status'] !== 'active') {
    echo json_encode([
        "success" => false,
        "error" => "Auction is not active"
    ]);
    exit;
}

// Check if bid amount is higher than current price
if ($amount <= $auction['current_price']) {
    echo json_encode([
        "success" => false,
        "error" => "Bid amount must be higher than current price"
    ]);
    exit;
}

// Insert bid
$insertSql = "INSERT INTO bids (auction_id, user_id, amount, created_at) VALUES (?, ?, ?, NOW())";
$insertStmt = $mysqli->prepare($insertSql);
$insertStmt->bind_param("iid", $auction_id, $user_id, $amount);

if ($insertStmt->execute()) {
    // Update auction current_price
    $updateSql = "UPDATE auctions SET current_price = ? WHERE id = ?";
    $updateStmt = $mysqli->prepare($updateSql);
    $updateStmt->bind_param("di", $amount, $auction_id);
    $updateStmt->execute();
    
    echo json_encode([
        "success" => true,
        "current_price" => $amount
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => "Failed to place bid: " . $mysqli->error
    ]);
}
?>

