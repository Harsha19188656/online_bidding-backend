<?php
/**
 * Comprehensive Backend Test
 * Tests database, APIs, and connectivity
 * URL: http://localhost/onlinebidding/test_all_backend.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Backend Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #1a1a1a; color: #fff; }
        .success { color: #4CAF50; padding: 10px; background: #2a2a2a; margin: 10px 0; border-left: 4px solid #4CAF50; }
        .error { color: #f44336; padding: 10px; background: #2a2a2a; margin: 10px 0; border-left: 4px solid #f44336; }
        .info { color: #2196F3; padding: 10px; background: #2a2a2a; margin: 10px 0; border-left: 4px solid #2196F3; }
        .warning { color: #FF9800; padding: 10px; background: #2a2a2a; margin: 10px 0; border-left: 4px solid #FF9800; }
        h1 { color: #FFC107; }
        h2 { color: #FFC107; margin-top: 30px; }
        pre { background: #2a2a2a; padding: 15px; border-radius: 5px; overflow-x: auto; color: #4CAF50; }
        a { color: #FFC107; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>🔍 Complete Backend Connection Test</h1>
    
<?php

echo "<h2>1️⃣ Database Connection Test</h2>";

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "onlinebidding";

// Test MySQL connection
$mysqli = new mysqli($db_host, $db_user, $db_pass);
if ($mysqli->connect_errno) {
    echo "<div class='error'>❌ MySQL Connection Failed: " . $mysqli->connect_error . "</div>";
    echo "<div class='warning'>💡 Solution: Start MySQL in XAMPP Control Panel</div>";
    exit;
}
echo "<div class='success'>✅ MySQL connection successful</div>";

// Test database exists
$result = $mysqli->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMAS WHERE SCHEMA_NAME = '$db_name'");
if ($result->num_rows == 0) {
    echo "<div class='error'>❌ Database '$db_name' does not exist!</div>";
    echo "<div class='warning'>💡 Solution: Run <a href='setup_complete.php'>setup_complete.php</a></div>";
    exit;
}
echo "<div class='success'>✅ Database '$db_name' exists</div>";

$mysqli->select_db($db_name);

// Test tables exist
$tables = ['users', 'products', 'auctions'];
foreach ($tables as $table) {
    $result = $mysqli->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "<div class='success'>✅ Table '$table' exists</div>";
    } else {
        echo "<div class='error'>❌ Table '$table' does not exist!</div>";
        echo "<div class='warning'>💡 Solution: Run <a href='setup_complete.php'>setup_complete.php</a></div>";
    }
}

echo "<h2>2️⃣ Data Check</h2>";

// Check laptops
$result = $mysqli->query("SELECT COUNT(*) as count FROM products WHERE category = 'laptop'");
$row = $result->fetch_assoc();
$laptop_count = $row['count'];
if ($laptop_count > 0) {
    echo "<div class='success'>✅ Found $laptop_count laptop(s) in database</div>";
} else {
    echo "<div class='error'>❌ No laptops in database!</div>";
    echo "<div class='warning'>💡 Solution: Run <a href='setup_complete.php'>setup_complete.php</a></div>";
}

// Check mobiles
$result = $mysqli->query("SELECT COUNT(*) as count FROM products WHERE category = 'mobile'");
$row = $result->fetch_assoc();
$mobile_count = $row['count'];
if ($mobile_count > 0) {
    echo "<div class='success'>✅ Found $mobile_count mobile(s) in database</div>";
} else {
    echo "<div class='error'>❌ No mobiles in database!</div>";
    echo "<div class='warning'>💡 Solution: Run <a href='setup_complete.php'>setup_complete.php</a> or <a href='ensure_mobile_data.php'>ensure_mobile_data.php</a></div>";
}

// Check auctions
$result = $mysqli->query("SELECT COUNT(*) as count FROM auctions a JOIN products p ON p.id = a.product_id WHERE p.category IN ('laptop', 'mobile')");
$row = $result->fetch_assoc();
$auction_count = $row['count'];
echo "<div class='info'>📊 Total auctions for laptops + mobiles: $auction_count</div>";

echo "<h2>3️⃣ API Endpoint Test</h2>";

$base_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);

echo "<div class='info'>🔗 <a href='api/auctions/list.php?category=laptop' target='_blank'>Test Laptop API</a></div>";
echo "<div class='info'>🔗 <a href='api/auctions/list.php?category=mobile' target='_blank'>Test Mobile API</a></div>";

// Test laptop API
echo "<h3>Laptop API Response:</h3>";
$laptop_api_url = __DIR__ . '/api/auctions/list.php?category=laptop';
$laptop_api_response = file_get_contents($base_url . '/api/auctions/list.php?category=laptop');
if ($laptop_api_response) {
    $laptop_data = json_decode($laptop_api_response, true);
    if ($laptop_data && isset($laptop_data['success']) && $laptop_data['success']) {
        $laptop_items_count = count($laptop_data['items'] ?? []);
        echo "<div class='success'>✅ Laptop API working! Returns $laptop_items_count item(s)</div>";
    } else {
        echo "<div class='error'>❌ Laptop API returned error or empty data</div>";
        echo "<pre>" . htmlspecialchars($laptop_api_response) . "</pre>";
    }
} else {
    echo "<div class='error'>❌ Could not access Laptop API</div>";
}

// Test mobile API
echo "<h3>Mobile API Response:</h3>";
$mobile_api_response = file_get_contents($base_url . '/api/auctions/list.php?category=mobile');
if ($mobile_api_response) {
    $mobile_data = json_decode($mobile_api_response, true);
    if ($mobile_data && isset($mobile_data['success']) && $mobile_data['success']) {
        $mobile_items_count = count($mobile_data['items'] ?? []);
        echo "<div class='success'>✅ Mobile API working! Returns $mobile_items_count item(s)</div>";
    } else {
        echo "<div class='error'>❌ Mobile API returned error or empty data</div>";
        echo "<pre>" . htmlspecialchars($mobile_api_response) . "</pre>";
    }
} else {
    echo "<div class='error'>❌ Could not access Mobile API</div>";
}

echo "<h2>4️⃣ Quick Fix</h2>";

if ($laptop_count == 0 || $mobile_count == 0) {
    echo "<div class='warning'>";
    echo "<strong>⚠️ Missing Data Detected!</strong><br>";
    echo "Click here to fix: <a href='setup_complete.php' style='color: #FFC107; font-weight: bold;'>🚀 Run Complete Setup</a>";
    echo "</div>";
}

$mysqli->close();

?>

    <div class='info'>
        <h3>📋 Summary</h3>
        <p><strong>If backend is not connecting, check:</strong></p>
        <ol>
            <li>✅ XAMPP Apache is running (green in XAMPP Control Panel)</li>
            <li>✅ XAMPP MySQL is running (green in XAMPP Control Panel)</li>
            <li>✅ Database has data (run setup_complete.php if missing)</li>
            <li>✅ API endpoints work in browser (test links above)</li>
            <li>✅ Android app BASE_URL is correct in RetrofitInstance.kt</li>
            <li>✅ Phone and computer on same WiFi (for physical device)</li>
        </ol>
    </div>

</body>
</html>

