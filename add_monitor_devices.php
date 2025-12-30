<?php
// Script to add monitor devices to the database
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

// Monitor devices to add
$monitors = [
    [
        'title' => 'Samsung Odyssey G9 49"',
        'description' => 'Ultra-wide curved gaming monitor with 49-inch display and premium features',
        'category' => 'monitor',
        'image_url' => 'https://example.com/samsungodyssey.jpg',
        'specs' => json_encode([
            'Size' => '49"',
            'Resolution' => '5120x1440',
            'Refresh Rate' => '240Hz',
            'Panel' => 'QLED',
            'Curvature' => '1000R'
        ]),
        'condition_label' => 'Premium Device',
        'base_price' => 95000.00
    ],
    [
        'title' => 'LG UltraFine 5K 27"',
        'description' => 'Professional 5K display with 27-inch screen and exceptional color accuracy',
        'category' => 'monitor',
        'image_url' => 'https://example.com/lgultrafine.jpg',
        'specs' => json_encode([
            'Size' => '27"',
            'Resolution' => '5120x2880',
            'Panel' => 'IPS',
            'Color' => 'P3 Wide Color',
            'Connectivity' => 'Thunderbolt 3'
        ]),
        'condition_label' => 'Premium Device',
        'base_price' => 68000.00
    ],
    [
        'title' => 'Dell UltraSharp U3423WE',
        'description' => 'Ultra-wide curved monitor with 34-inch display and USB-C connectivity',
        'category' => 'monitor',
        'image_url' => 'https://example.com/dellultrasharp.jpg',
        'specs' => json_encode([
            'Size' => '34"',
            'Resolution' => '3440x1440',
            'Panel' => 'IPS',
            'Curvature' => '1800R',
            'Connectivity' => 'USB-C, HDMI, DisplayPort'
        ]),
        'condition_label' => 'Premium Device',
        'base_price' => 52000.00
    ]
];

$results = [];
$successCount = 0;
$errorCount = 0;

foreach ($monitors as $monitor) {
    // Check if product already exists
    $checkStmt = $mysqli->prepare("SELECT id FROM products WHERE title = ? AND category = ?");
    $checkStmt->bind_param("ss", $monitor['title'], $monitor['category']);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        $results[] = [
            'title' => $monitor['title'],
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
        $monitor['title'],
        $monitor['description'],
        $monitor['category'],
        $monitor['image_url'],
        $monitor['specs'],
        $monitor['condition_label'],
        $monitor['base_price']
    );
    
    if ($stmt->execute()) {
        $product_id = $mysqli->insert_id;
        
        // Create auction for this product
        $auctionStmt = $mysqli->prepare("INSERT INTO auctions (product_id, start_price, current_price, status) VALUES (?, ?, ?, 'scheduled')");
        $startPrice = $monitor['base_price'] * 0.8; // Start at 80% of base price
        $auctionStmt->bind_param("idd", $product_id, $startPrice, $monitor['base_price']);
        
        if ($auctionStmt->execute()) {
            $results[] = [
                'title' => $monitor['title'],
                'status' => 'success',
                'product_id' => $product_id,
                'auction_id' => $mysqli->insert_id,
                'message' => 'Product and auction created successfully'
            ];
            $successCount++;
        } else {
            $results[] = [
                'title' => $monitor['title'],
                'status' => 'partial',
                'product_id' => $product_id,
                'message' => 'Product created but auction failed: ' . $auctionStmt->error
            ];
            $errorCount++;
        }
        $auctionStmt->close();
    } else {
        $results[] = [
            'title' => $monitor['title'],
            'status' => 'error',
            'message' => 'Failed to create product: ' . $stmt->error
        ];
        $errorCount++;
    }
    
    $stmt->close();
}

$mysqli->close();

$totalCount = count($monitors);
echo json_encode([
    'success' => true,
    'message' => "Processed $totalCount monitor devices",
    'added' => $successCount,
    'errors' => $errorCount,
    'results' => $results
], JSON_PRETTY_PRINT);
?>

