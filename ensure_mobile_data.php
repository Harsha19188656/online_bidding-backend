<?php
/**
 * Ensure Mobile Data Exists
 * Run this to insert mobile data if missing
 * URL: http://localhost/onlinebidding/ensure_mobile_data.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ensure Mobile Data</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #1a1a1a; color: #fff; }
        .success { color: #4CAF50; padding: 10px; background: #2a2a2a; margin: 10px 0; border-left: 4px solid #4CAF50; }
        .error { color: #f44336; padding: 10px; background: #2a2a2a; margin: 10px 0; border-left: 4px solid #f44336; }
        .info { color: #2196F3; padding: 10px; background: #2a2a2a; margin: 10px 0; border-left: 4px solid #2196F3; }
        h1 { color: #FFC107; }
    </style>
</head>
<body>
    <h1>📱 Ensure Mobile Data Exists</h1>
    
<?php

require __DIR__ . '/db.php';

// Check if database connection works
if ($mysqli->connect_errno) {
    echo "<div class='error'>❌ Database connection failed!</div>";
    exit;
}

// Check if database exists
$db_name = "onlinebidding";
$result = $mysqli->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMAS WHERE SCHEMA_NAME = '$db_name'");
if ($result->num_rows == 0) {
    echo "<div class='error'>❌ Database 'onlinebidding' does not exist! Run setup_complete.php first.</div>";
    exit;
}

$mysqli->select_db($db_name);

// Check if products table exists
$result = $mysqli->query("SHOW TABLES LIKE 'products'");
if ($result->num_rows == 0) {
    echo "<div class='error'>❌ Products table does not exist! Run setup_complete.php first.</div>";
    exit;
}

// Check current mobile count
$result = $mysqli->query("SELECT COUNT(*) as count FROM products WHERE category = 'mobile'");
$row = $result->fetch_assoc();
$current_count = $row['count'];

echo "<div class='info'>📊 Current mobile count in database: $current_count</div>";

if ($current_count > 0) {
    echo "<div class='success'>✅ Mobile data already exists!</div>";
    
    // Show existing mobiles
    $result = $mysqli->query("SELECT id, title, base_price FROM products WHERE category = 'mobile'");
    echo "<div class='info'><strong>Existing Mobiles:</strong></div>";
    while ($mobile = $result->fetch_assoc()) {
        echo "<div class='info'>- {$mobile['title']} (ID: {$mobile['id']}, Price: ₹{$mobile['base_price']})</div>";
    }
} else {
    echo "<div class='info'>📦 Inserting mobile data...</div>";
    
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
    FROM products WHERE category = 'mobile' AND id NOT IN (SELECT product_id FROM auctions);
    ";
    
    if ($mysqli->query($auctions_sql)) {
        $affected = $mysqli->affected_rows;
        echo "<div class='success'>✅ Created $affected auction(s) for mobiles</div>";
    }
    
    // Verify
    $result = $mysqli->query("SELECT COUNT(*) as count FROM products WHERE category = 'mobile'");
    $row = $result->fetch_assoc();
    echo "<div class='success'>✅ Verification: {$row['count']} mobile(s) now in database</div>";
}

$mysqli->close();

?>

    <div class='info'>
        <h3>🧪 Test API</h3>
        <p><a href="api/auctions/list.php?category=mobile" target="_blank" style="color: #FFC107;">Test Mobile API: api/auctions/list.php?category=mobile</a></p>
        <p>Should return JSON with mobile data.</p>
    </div>

</body>
</html>

