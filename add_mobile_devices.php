<?php
// Script to add mobile devices to the database
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

// Mobile devices to add
$mobiles = [
    [
        'title' => 'iPhone 15 Pro Max',
        'description' => 'Latest iPhone with A17 Pro chip, Titanium design, and advanced camera system',
        'category' => 'mobile',
        'image_url' => 'https://example.com/iphone15promax.jpg',
        'specs' => json_encode([
            'Processor' => 'A17 Pro Chip',
            'Storage' => '512GB',
            'Material' => 'Titanium',
            'Display' => '6.7-inch Super Retina XDR',
            'Camera' => '48MP Main + 12MP Ultra Wide + 12MP Telephoto'
        ]),
        'condition_label' => 'Excellent',
        'base_price' => 128000.00
    ],
    [
        'title' => 'Samsung Galaxy S24 Ultra',
        'description' => 'Flagship Samsung phone with Snapdragon 8 Gen 3, S Pen, and premium features',
        'category' => 'mobile',
        'image_url' => 'https://example.com/samsungs24ultra.jpg',
        'specs' => json_encode([
            'Processor' => 'Snapdragon 8 Gen 3',
            'Storage' => '256GB',
            'S Pen' => 'Included',
            'Display' => '6.8-inch Dynamic AMOLED 2X',
            'Camera' => '200MP Main + 50MP Periscope + 12MP Ultra Wide + 10MP Telephoto'
        ]),
        'condition_label' => 'Excellent',
        'base_price' => 98000.00
    ],
    [
        'title' => 'OnePlus 12 Pro',
        'description' => 'Premium OnePlus device with Snapdragon 8 Gen 3 and ultra-fast charging',
        'category' => 'mobile',
        'image_url' => 'https://example.com/oneplus12pro.jpg',
        'specs' => json_encode([
            'Processor' => 'Snapdragon 8 Gen 3',
            'Storage' => '256GB',
            'Charging' => 'Fast Charging',
            'Display' => '6.82-inch LTPO AMOLED',
            'Camera' => '50MP Main + 64MP Periscope + 48MP Ultra Wide'
        ]),
        'condition_label' => 'Excellent',
        'base_price' => 54000.00
    ]
];

$results = [];
$successCount = 0;
$errorCount = 0;

foreach ($mobiles as $mobile) {
    // Check if product already exists
    $checkStmt = $mysqli->prepare("SELECT id FROM products WHERE title = ? AND category = ?");
    $checkStmt->bind_param("ss", $mobile['title'], $mobile['category']);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        $results[] = [
            'title' => $mobile['title'],
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
        $mobile['title'],
        $mobile['description'],
        $mobile['category'],
        $mobile['image_url'],
        $mobile['specs'],
        $mobile['condition_label'],
        $mobile['base_price']
    );
    
    if ($stmt->execute()) {
        $product_id = $mysqli->insert_id;
        
        // Create auction for this product
        $auctionStmt = $mysqli->prepare("INSERT INTO auctions (product_id, start_price, current_price, status) VALUES (?, ?, ?, 'scheduled')");
        $startPrice = $mobile['base_price'] * 0.8; // Start at 80% of base price
        $auctionStmt->bind_param("idd", $product_id, $startPrice, $mobile['base_price']);
        
        if ($auctionStmt->execute()) {
            $results[] = [
                'title' => $mobile['title'],
                'status' => 'success',
                'product_id' => $product_id,
                'auction_id' => $mysqli->insert_id,
                'message' => 'Product and auction created successfully'
            ];
            $successCount++;
        } else {
            $results[] = [
                'title' => $mobile['title'],
                'status' => 'partial',
                'product_id' => $product_id,
                'message' => 'Product created but auction failed: ' . $auctionStmt->error
            ];
            $errorCount++;
        }
        $auctionStmt->close();
    } else {
        $results[] = [
            'title' => $mobile['title'],
            'status' => 'error',
            'message' => 'Failed to create product: ' . $stmt->error
        ];
        $errorCount++;
    }
    
    $stmt->close();
}

$mysqli->close();

$totalCount = count($mobiles);
echo json_encode([
    'success' => true,
    'message' => "Processed $totalCount mobile devices",
    'added' => $successCount,
    'errors' => $errorCount,
    'results' => $results
], JSON_PRETTY_PRINT);
?>

