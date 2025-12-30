<?php
// Script to add tablet devices to the database
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

// Tablet devices to add
$tablets = [
    [
        'title' => 'iPad Pro 12.9" M2',
        'description' => 'Apple iPad Pro with M2 chip, 12.9-inch Liquid Retina XDR display',
        'category' => 'tablet',
        'image_url' => 'https://example.com/ipadpro.jpg',
        'specs' => json_encode([
            'Size' => '12.9"',
            'Processor' => 'Apple M2',
            'Display' => 'Liquid Retina XDR',
            'Storage' => '256GB',
            'Connectivity' => 'Wi-Fi + Cellular'
        ]),
        'condition_label' => 'Excellent',
        'base_price' => 85000.00
    ],
    [
        'title' => 'Samsung Galaxy Tab S9',
        'description' => 'Premium Android tablet with Snapdragon 8 Gen 2 and S Pen included',
        'category' => 'tablet',
        'image_url' => 'https://example.com/galaxytabs9.jpg',
        'specs' => json_encode([
            'Size' => '11"',
            'Processor' => 'Snapdragon 8 Gen 2',
            'Display' => 'Dynamic AMOLED 2X',
            'Storage' => '256GB',
            'S Pen' => 'Included'
        ]),
        'condition_label' => 'Excellent',
        'base_price' => 72000.00
    ],
    [
        'title' => 'Microsoft Surface Pro 9',
        'description' => '2-in-1 tablet with detachable keyboard, Intel Core i7 processor',
        'category' => 'tablet',
        'image_url' => 'https://example.com/surfacepro9.jpg',
        'specs' => json_encode([
            'Size' => '13"',
            'Processor' => 'Intel Core i7',
            'Display' => 'PixelSense',
            'Storage' => '256GB SSD',
            'Type' => '2-in-1'
        ]),
        'condition_label' => 'Excellent',
        'base_price' => 58000.00
    ]
];

$results = [];
$successCount = 0;
$errorCount = 0;

foreach ($tablets as $tablet) {
    // Check if product already exists
    $checkStmt = $mysqli->prepare("SELECT id FROM products WHERE title = ? AND category = ?");
    $checkStmt->bind_param("ss", $tablet['title'], $tablet['category']);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        $results[] = [
            'title' => $tablet['title'],
            'status' => 'skipped',
            'message' => 'Already exists in database'
        ];
        $checkStmt->close();
        continue;
    }
    $checkStmt->close();
    
    // Insert product
    $stmt = $mysqli->prepare("INSERT INTO products (title, description, category, image_url, specs, condition_label, base_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssd", 
        $tablet['title'],
        $tablet['description'],
        $tablet['category'],
        $tablet['image_url'],
        $tablet['specs'],
        $tablet['condition_label'],
        $tablet['base_price']
    );
    
    if ($stmt->execute()) {
        $product_id = $mysqli->insert_id;
        
        // Create auction for this product
        $auctionStmt = $mysqli->prepare("INSERT INTO auctions (product_id, start_price, current_price, status) VALUES (?, ?, ?, 'scheduled')");
        $startPrice = $tablet['base_price'] * 0.8; // Start at 80% of base price
        $auctionStmt->bind_param("idd", $product_id, $startPrice, $tablet['base_price']);
        
        if ($auctionStmt->execute()) {
            $results[] = [
                'title' => $tablet['title'],
                'status' => 'success',
                'product_id' => $product_id,
                'auction_id' => $mysqli->insert_id,
                'message' => 'Product and auction created successfully'
            ];
            $successCount++;
        } else {
            $results[] = [
                'title' => $tablet['title'],
                'status' => 'partial',
                'product_id' => $product_id,
                'message' => 'Product created but auction failed: ' . $auctionStmt->error
            ];
            $errorCount++;
        }
        $auctionStmt->close();
    } else {
        $results[] = [
            'title' => $tablet['title'],
            'status' => 'error',
            'message' => 'Failed to create product: ' . $stmt->error
        ];
        $errorCount++;
    }
    
    $stmt->close();
}

$mysqli->close();

$totalCount = count($tablets);
echo json_encode([
    'success' => true,
    'message' => "Processed $totalCount tablet devices",
    'added' => $successCount,
    'errors' => $errorCount,
    'results' => $results
], JSON_PRETTY_PRINT);
?>

