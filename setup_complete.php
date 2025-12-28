<?php
/**
 * Complete Automated Setup Script
 * Run this once: http://10.148.199.81/onlinebidding/setup_complete.php
 * Or: http://localhost/onlinebidding/setup_complete.php
 * This will create database, tables, and insert test data
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Online Bidding - Complete Setup</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #1a1a1a; color: #fff; }
        .success { color: #4CAF50; padding: 10px; background: #2a2a2a; margin: 10px 0; border-left: 4px solid #4CAF50; }
        .error { color: #f44336; padding: 10px; background: #2a2a2a; margin: 10px 0; border-left: 4px solid #f44336; }
        .info { color: #2196F3; padding: 10px; background: #2a2a2a; margin: 10px 0; border-left: 4px solid #2196F3; }
        h1 { color: #FFC107; }
        pre { background: #2a2a2a; padding: 15px; border-radius: 5px; overflow-x: auto; }
        a { color: #FFC107; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>🚀 Online Bidding - Complete Setup</h1>
    
<?php

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "onlinebidding";

$errors = [];
$success = [];

// Step 1: Connect to MySQL (without selecting database)
$mysqli = new mysqli($db_host, $db_user, $db_pass);
if ($mysqli->connect_errno) {
    echo "<div class='error'>❌ MySQL Connection Failed: " . $mysqli->connect_error . "</div>";
    echo "<div class='error'>💡 Make sure XAMPP MySQL is running!</div>";
    exit;
}
echo "<div class='success'>✅ Connected to MySQL</div>";

// Step 2: Create database
$sql = "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($mysqli->query($sql)) {
    echo "<div class='success'>✅ Database '$db_name' created/verified</div>";
} else {
    echo "<div class='error'>❌ Database creation failed: " . $mysqli->error . "</div>";
    exit;
}

// Step 3: Select database
$mysqli->select_db($db_name);

// Step 4: Create tables
$tables_sql = file_get_contents(__DIR__ . '/schema.sql');
if ($tables_sql) {
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $tables_sql)));
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^CREATE DATABASE|^USE /i', $statement)) {
            if ($mysqli->query($statement)) {
                if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches)) {
                    echo "<div class='success'>✅ Table created: " . $matches[1] . "</div>";
                }
            } else {
                // Ignore "already exists" errors
                if (strpos($mysqli->error, 'already exists') === false && 
                    strpos($mysqli->error, 'Duplicate key name') === false) {
                    echo "<div class='info'>ℹ️ " . $mysqli->error . "</div>";
                }
            }
        }
    }
} else {
    echo "<div class='error'>❌ Could not read schema.sql</div>";
}

// Step 5: Insert test laptop data (delete existing first to avoid duplicates)
echo "<div class='info'>📦 Inserting test laptop data...</div>";

// Delete existing test laptops first
$mysqli->query("DELETE FROM auctions WHERE product_id IN (SELECT id FROM products WHERE category = 'laptop' AND title IN ('MacBook Pro 16\" M3', 'Dell XPS 15 OLED', 'ASUS ROG Zephyrus G16'))");
$mysqli->query("DELETE FROM products WHERE category = 'laptop' AND title IN ('MacBook Pro 16\" M3', 'Dell XPS 15 OLED', 'ASUS ROG Zephyrus G16')");

$laptops_sql = "
INSERT INTO products (title, description, category, image_url, specs, condition_label, base_price) VALUES
('MacBook Pro 16\" M3', 'Premium Apple laptop with M3 Max chip', 'laptop', 'https://example.com/macbook.jpg', '{\"Processor\": \"Apple M3 Max\", \"RAM\": \"48GB\", \"Storage\": \"1TB SSD\"}', 'Excellent', 185000.00),
('Dell XPS 15 OLED', 'Premium Dell laptop with OLED display', 'laptop', 'https://example.com/dell.jpg', '{\"Processor\": \"Intel i7-13700H\", \"RAM\": \"32GB\", \"Storage\": \"1TB SSD\"}', 'Very Good', 95000.00),
('ASUS ROG Zephyrus G16', 'Gaming laptop with Ryzen processor', 'laptop', 'https://example.com/asus.jpg', '{\"Processor\": \"Ryzen 9 7940HS\", \"RAM\": \"32GB\", \"Storage\": \"2TB SSD\"}', 'Excellent', 142000.00);
";

if ($mysqli->multi_query($laptops_sql)) {
    do {
        if ($result = $mysqli->store_result()) {
            $affected = $mysqli->affected_rows;
            if ($affected > 0) {
                echo "<div class='success'>✅ Inserted $affected laptop product(s)</div>";
            }
            $result->free();
        }
    } while ($mysqli->next_result());
}

// Step 5b: Insert test mobile data
echo "<div class='info'>📦 Inserting test mobile data...</div>";

// Delete existing test mobiles first
$mysqli->query("DELETE FROM auctions WHERE product_id IN (SELECT id FROM products WHERE category = 'mobile' AND title IN ('iPhone 15 Pro Max', 'Samsung Galaxy S24 Ultra', 'OnePlus 12 Pro'))");
$mysqli->query("DELETE FROM products WHERE category = 'mobile' AND title IN ('iPhone 15 Pro Max', 'Samsung Galaxy S24 Ultra', 'OnePlus 12 Pro')");

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

// Step 6: Insert auctions for laptops
$auctions_sql = "
INSERT INTO auctions (product_id, start_price, current_price, status, start_at, end_at)
SELECT id, base_price, base_price, 'live', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY)
FROM products WHERE category = 'laptop'
ON DUPLICATE KEY UPDATE current_price = VALUES(current_price);
";

if ($mysqli->query($auctions_sql)) {
    $affected = $mysqli->affected_rows;
    echo "<div class='success'>✅ Created/Updated $affected auction(s) for laptops</div>";
}

// Step 6b: Insert auctions for mobiles
$auctions_mobile_sql = "
INSERT INTO auctions (product_id, start_price, current_price, status, start_at, end_at)
SELECT id, base_price, base_price, 'live', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY)
FROM products WHERE category = 'mobile'
ON DUPLICATE KEY UPDATE current_price = VALUES(current_price);
";

if ($mysqli->query($auctions_mobile_sql)) {
    $affected = $mysqli->affected_rows;
    echo "<div class='success'>✅ Created/Updated $affected auction(s) for mobiles</div>";
}

// Step 7: Verify data
echo "<div class='info'>🔍 Verifying data...</div>";

$check_products = $mysqli->query("SELECT COUNT(*) as count FROM products WHERE category = 'laptop'");
if ($check_products) {
    $row = $check_products->fetch_assoc();
    echo "<div class='success'>✅ Found " . $row['count'] . " laptop product(s) in database</div>";
}

$check_auctions = $mysqli->query("SELECT COUNT(*) as count FROM auctions a JOIN products p ON p.id = a.product_id WHERE p.category = 'laptop'");
if ($check_auctions) {
    $row = $check_auctions->fetch_assoc();
    echo "<div class='success'>✅ Found " . $row['count'] . " laptop auction(s) in database</div>";
}

$check_mobiles = $mysqli->query("SELECT COUNT(*) as count FROM products WHERE category = 'mobile'");
if ($check_mobiles) {
    $row = $check_mobiles->fetch_assoc();
    echo "<div class='success'>✅ Found " . $row['count'] . " mobile product(s) in database</div>";
}

$check_mobile_auctions = $mysqli->query("SELECT COUNT(*) as count FROM auctions a JOIN products p ON p.id = a.product_id WHERE p.category = 'mobile'");
if ($check_mobile_auctions) {
    $row = $check_mobile_auctions->fetch_assoc();
    echo "<div class='success'>✅ Found " . $row['count'] . " mobile auction(s) in database</div>";
}

$mysqli->close();

?>

    <div class='success'>
        <h2>✅ Setup Complete!</h2>
        <p><strong>Next Steps:</strong></p>
        <ol>
            <li>Test Registration API: <a href="api/register.php" target="_blank">api/register.php</a></li>
            <li>Test Laptops API: <a href="api/auctions/list.php?category=laptop" target="_blank">api/auctions/list.php?category=laptop</a></li>
            <li>Update Android app IP in <code>RetrofitInstance.kt</code> if needed</li>
            <li>Build and run your Android app</li>
            <li>Navigate to Laptops screen - you should see 3 laptops!</li>
        </ol>
    </div>

    <div class='info'>
        <h3>📊 Database Summary</h3>
        <p>Database: <strong><?php echo $db_name; ?></strong></p>
        <p>Host: <strong><?php echo $db_host; ?></strong></p>
        <p>All data stored at: <code>C:\xampp\htdocs\onlinebidding</code></p>
    </div>

</body>
</html>
