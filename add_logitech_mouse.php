<?php
// Script to add Logitech mouse to the database
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require __DIR__ . '/db.php';

if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database connection failed: " . $mysqli->connect_error]);
    exit;
}

if (!$mysqli->select_db("onlinebidding")) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database selection failed"]);
    exit;
}

// Logitech mouse product
$mouse = [
    'title' => 'Logitech mouse',
    'description' => 'Wireless, 6 Buttons, Black - Premium wireless mouse with ergonomic design',
    'category' => 'computer', // Adding as computer accessory
    'image_url' => 'https://example.com/logitechmouse.jpg',
    'specs' => json_encode([
        'Type' => 'Wireless',
        'Buttons' => '6 Buttons',
        'Color' => 'Black',
        'Connectivity' => 'Wireless',
        'Design' => 'Ergonomic'
    ]),
    'condition_label' => 'Excellent',
    'base_price' => 4000.00
];

// Check if product already exists
$checkStmt = $mysqli->prepare("SELECT id FROM products WHERE title = ? AND category = ?");
$checkStmt->bind_param("ss", $mouse['title'], $mouse['category']);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    $checkStmt->close();
    echo json_encode([
        'success' => true,
        'message' => 'Logitech mouse already exists in database',
        'status' => 'skipped'
    ], JSON_PRETTY_PRINT);
    exit;
}
$checkStmt->close();

// Insert product
$stmt = $mysqli->prepare("INSERT INTO products (title, description, category, image_url, specs, condition_label, base_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssd", 
    $mouse['title'],
    $mouse['description'],
    $mouse['category'],
    $mouse['image_url'],
    $mouse['specs'],
    $mouse['condition_label'],
    $mouse['base_price']
);

if ($stmt->execute()) {
    $product_id = $mysqli->insert_id;
    
    // Create auction for this product
    $auctionStmt = $mysqli->prepare("INSERT INTO auctions (product_id, start_price, current_price, status) VALUES (?, ?, ?, 'scheduled')");
    $startPrice = $mouse['base_price'] * 0.8; // Start at 80% of base price
    $auctionStmt->bind_param("idd", $product_id, $startPrice, $mouse['base_price']);
    
    if ($auctionStmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Logitech mouse added successfully',
            'product_id' => $product_id,
            'auction_id' => $mysqli->insert_id,
            'title' => $mouse['title'],
            'price' => $mouse['base_price']
        ], JSON_PRETTY_PRINT);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Product created but auction failed',
            'product_id' => $product_id,
            'error' => $auctionStmt->error
        ], JSON_PRETTY_PRINT);
    }
    $auctionStmt->close();
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to create product: ' . $stmt->error
    ], JSON_PRETTY_PRINT);
}

$stmt->close();
$mysqli->close();
?>

