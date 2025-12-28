<?php
/**
 * Fix Mobile Connection - Insert Mobile Data
 * Run this: http://localhost/onlinebidding/fix_mobile_connection.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Mobile Connection</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #1a1a1a; color: #fff; }
        .success { color: #4CAF50; padding: 10px; background: #2a2a2a; margin: 10px 0; border-left: 4px solid #4CAF50; }
        .error { color: #f44336; padding: 10px; background: #2a2a2a; margin: 10px 0; border-left: 4px solid #f44336; }
        .info { color: #2196F3; padding: 10px; background: #2a2a2a; margin: 10px 0; border-left: 4px solid #2196F3; }
        h1 { color: #FFC107; }
        pre { background: #2a2a2a; padding: 15px; border-radius: 5px; overflow-x: auto; color: #4CAF50; }
    </style>
</head>
<body>
    <h1>📱 Fix Mobile Connection</h1>
    
<?php

require __DIR__ . '/db.php';

if ($mysqli->connect_errno) {
    echo "<div class='error'>❌ Database connection failed!</div>";
    exit;
}

$mysqli->select_db("onlinebidding");

// Check current mobile count
$result = $mysqli->query("SELECT COUNT(*) as count FROM products WHERE category = 'mobile'");
$row = $result->fetch_assoc();
$current_count = $row['count'];

echo "<div class='info'>📊 Current mobile count: $current_count</div>";

if ($current_count > 0) {
    echo "<div class='success'>✅ Mobile data already exists!</div>";
    
    // Show existing mobiles
    $result = $mysqli->query("SELECT id, title, base_price FROM products WHERE category = 'mobile'");
    echo "<div class='info'><strong>Existing Mobiles:</strong></div>";
    while ($mobile = $result->fetch_assoc()) {
        echo "<div class='success'>- {$mobile['title']} (ID: {$mobile['id']}, Price: ₹" . number_format($mobile['base_price'], 0) . ")</div>";
    }
    
    // Check auctions
    $result = $mysqli->query("SELECT COUNT(*) as count FROM auctions a JOIN products p ON p.id = a.product_id WHERE p.category = 'mobile'");
    $row = $result->fetch_assoc();
    $auction_count = $row['count'];
    
    if ($auction_count == 0) {
        echo "<div class='info'>📦 Creating auctions for mobiles...</div>";
        $auctions_sql = "
        INSERT INTO auctions (product_id, start_price, current_price, status, start_at, end_at)
        SELECT id, base_price, base_price, 'live', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY)
        FROM products WHERE category = 'mobile' AND id NOT IN (SELECT product_id FROM auctions WHERE product_id IS NOT NULL);
        ";
        
        if ($mysqli->query($auctions_sql)) {
            $affected = $mysqli->affected_rows;
            echo "<div class='success'>✅ Created $affected auction(s) for mobiles</div>";
        }
    } else {
        echo "<div class='success'>✅ Found $auction_count mobile auction(s)</div>";
    }
} else {
    echo "<div class='info'>📦 Inserting mobile data...</div>";
    
    // Delete any existing mobiles first
    $mysqli->query("DELETE FROM auctions WHERE product_id IN (SELECT id FROM products WHERE category = 'mobile')");
    $mysqli->query("DELETE FROM products WHERE category = 'mobile'");
    
    // Insert mobile products
    $mobiles_sql = "
    INSERT INTO products (title, description, category, image_url, specs, condition_label, base_price) VALUES
    ('iPhone 15 Pro Max', 'Premium Apple smartphone with A17 Pro chip', 'mobile', 'https://example.com/iphone.jpg', '{\"Processor\": \"A17 Pro Chip\", \"Storage\": \"512GB\", \"Display\": \"6.7\\\" Super Retina XDR\"}', 'Excellent', 128000.00),
    ('Samsung Galaxy S24 Ultra', 'Premium Samsung smartphone with Snapdragon 8 Gen 3', 'mobile', 'https://example.com/samsung.jpg', '{\"Processor\": \"Snapdragon 8 Gen 3\", \"Storage\": \"256GB\", \"Display\": \"6.8\\\" Dynamic AMOLED\"}', 'Very Good', 98000.00),
    ('OnePlus 12 Pro', 'Premium OnePlus smartphone with fast charging', 'mobile', 'https://example.com/oneplus.jpg', '{\"Processor\": \"Snapdragon 8 Gen 3\", \"Storage\": \"256GB\", \"Display\": \"6.7\\\" AMOLED\"}', 'Excellent', 54000.00);
    ";
    
    if ($mysqli->multi_query($mobiles_sql)) {
        do {
            if ($result = $mysqli->store_result()) {
                $affected = $mysqli->affected_rows;
                if ($affected > 0) {
                    echo "<div class='success'>✅ Inserted $affected mobile product(s)</div>";
                }
                $result->free();
            }
        } while ($mysqli->next_result());
    }
    
    // Insert auctions for mobiles
    $auctions_sql = "
    INSERT INTO auctions (product_id, start_price, current_price, status, start_at, end_at)
    SELECT id, base_price, base_price, 'live', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY)
    FROM products WHERE category = 'mobile';
    ";
    
    if ($mysqli->query($auctions_sql)) {
        $affected = $mysqli->affected_rows;
        echo "<div class='success'>✅ Created $affected auction(s) for mobiles</div>";
    }
}

// Verify final state
echo "<div class='info'>🔍 Final Verification:</div>";

$result = $mysqli->query("SELECT COUNT(*) as count FROM products WHERE category = 'mobile'");
$row = $result->fetch_assoc();
echo "<div class='success'>✅ Products: {$row['count']} mobile(s) in database</div>";

$result = $mysqli->query("SELECT COUNT(*) as count FROM auctions a JOIN products p ON p.id = a.product_id WHERE p.category = 'mobile'");
$row = $result->fetch_assoc();
echo "<div class='success'>✅ Auctions: {$row['count']} mobile auction(s) in database</div>";

// Test API
echo "<div class='info'>🧪 Testing Mobile API...</div>";
$api_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/api/auctions/list.php?category=mobile";
$api_response = @file_get_contents($api_url);
if ($api_response) {
    $api_data = json_decode($api_response, true);
    if ($api_data && isset($api_data['success']) && $api_data['success']) {
        $items_count = count($api_data['items'] ?? []);
        echo "<div class='success'>✅ Mobile API working! Returns $items_count item(s)</div>";
        echo "<div class='info'><a href='api/auctions/list.php?category=mobile' target='_blank' style='color: #FFC107;'>View API Response</a></div>";
    } else {
        echo "<div class='error'>❌ Mobile API returned error</div>";
        echo "<pre>" . htmlspecialchars($api_response) . "</pre>";
    }
} else {
    echo "<div class='error'>❌ Could not access Mobile API</div>";
}

$mysqli->close();

?>

    <div class='success'>
        <h3>✅ Done!</h3>
        <p>Now rebuild and run your Android app. MobileList should connect!</p>
    </div>

</body>
</html>

