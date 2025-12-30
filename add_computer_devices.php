<?php
// Script to add computer devices to the database
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

// Computer devices to add
$computers = [
    [
        'title' => 'Custom Gaming PC RTX',
        'description' => 'High-performance gaming PC with RTX graphics, 64GB DDR5 RAM, and massive storage',
        'category' => 'computer',
        'image_url' => 'https://example.com/gamingpc.jpg',
        'specs' => json_encode([
            'RAM' => '64GB DDR5',
            'Storage' => '2TB Gen5 NVMe + 4TB HDD',
            'Graphics' => 'RTX 4090',
            'Processor' => 'Intel Core i9-13900K',
            'Cooling' => 'Liquid Cooling'
        ]),
        'condition_label' => 'Excellent',
        'base_price' => 285000.00
    ],
    [
        'title' => 'Mac Studio M2 Ultra',
        'description' => 'Apple Mac Studio with M2 Ultra chip, 192GB unified memory, and 4TB SSD',
        'category' => 'computer',
        'image_url' => 'https://example.com/macstudio.jpg',
        'specs' => json_encode([
            'Memory' => '192GB Unified Memory',
            'Storage' => '4TB SSD',
            'Processor' => 'Apple M2 Ultra',
            'Graphics' => '76-core GPU',
            'Ports' => 'Thunderbolt 4, USB-A, HDMI'
        ]),
        'condition_label' => 'Excellent',
        'base_price' => 310000.00
    ],
    [
        'title' => 'HP Z8 G5 Workstation',
        'description' => 'Professional workstation with 128GB ECC DDR5 RAM and 4TB NVMe RAID storage',
        'category' => 'computer',
        'image_url' => 'https://example.com/hpz8.jpg',
        'specs' => json_encode([
            'RAM' => '128GB ECC DDR5',
            'Storage' => '4TB NVMe RAID',
            'Processor' => 'Intel Xeon W-3375',
            'Graphics' => 'NVIDIA RTX A6000',
            'Form Factor' => 'Tower'
        ]),
        'condition_label' => 'Excellent',
        'base_price' => 195000.00
    ]
];

$results = [];
$successCount = 0;
$errorCount = 0;

foreach ($computers as $computer) {
    // Check if product already exists
    $checkStmt = $mysqli->prepare("SELECT id FROM products WHERE title = ? AND category = ?");
    $checkStmt->bind_param("ss", $computer['title'], $computer['category']);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        $results[] = [
            'title' => $computer['title'],
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
        $computer['title'],
        $computer['description'],
        $computer['category'],
        $computer['image_url'],
        $computer['specs'],
        $computer['condition_label'],
        $computer['base_price']
    );
    
    if ($stmt->execute()) {
        $product_id = $mysqli->insert_id;
        
        // Create auction for this product
        $auctionStmt = $mysqli->prepare("INSERT INTO auctions (product_id, start_price, current_price, status) VALUES (?, ?, ?, 'scheduled')");
        $startPrice = $computer['base_price'] * 0.8; // Start at 80% of base price
        $auctionStmt->bind_param("idd", $product_id, $startPrice, $computer['base_price']);
        
        if ($auctionStmt->execute()) {
            $results[] = [
                'title' => $computer['title'],
                'status' => 'success',
                'product_id' => $product_id,
                'auction_id' => $mysqli->insert_id,
                'message' => 'Product and auction created successfully'
            ];
            $successCount++;
        } else {
            $results[] = [
                'title' => $computer['title'],
                'status' => 'partial',
                'product_id' => $product_id,
                'message' => 'Product created but auction failed: ' . $auctionStmt->error
            ];
            $errorCount++;
        }
        $auctionStmt->close();
    } else {
        $results[] = [
            'title' => $computer['title'],
            'status' => 'error',
            'message' => 'Failed to create product: ' . $stmt->error
        ];
        $errorCount++;
    }
    
    $stmt->close();
}

$mysqli->close();

$totalCount = count($computers);
echo json_encode([
    'success' => true,
    'message' => "Processed $totalCount computer devices",
    'added' => $successCount,
    'errors' => $errorCount,
    'results' => $results
], JSON_PRETTY_PRINT);
?>

