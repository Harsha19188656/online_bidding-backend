<?php
/**
 * Backend Connection Test Script
 * Test this to verify backend is working for Create Account and LaptopList
 * URL: http://10.148.199.81/onlinebidding/test_backend.php
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
        table { width: 100%; border-collapse: collapse; margin: 10px 0; background: #2a2a2a; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #444; }
        th { background: #333; color: #FFC107; }
    </style>
</head>
<body>
    <h1>🔍 Backend Connection Test</h1>
    <p>This script tests if your backend is properly configured for Create Account and LaptopList screens.</p>

<?php

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "onlinebidding";

$tests_passed = 0;
$tests_failed = 0;

// Test 1: Database Connection
echo "<h2>1️⃣ Database Connection Test</h2>";
try {
    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($mysqli->connect_errno) {
        echo "<div class='error'>❌ Database Connection Failed: " . $mysqli->connect_error . "</div>";
        $tests_failed++;
    } else {
        echo "<div class='success'>✅ Database connection successful!</div>";
        echo "<div class='info'>📊 Connected to: $db_name @ $db_host</div>";
        $tests_passed++;
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Database Connection Exception: " . $e->getMessage() . "</div>";
    $tests_failed++;
    $mysqli = null;
}

if ($mysqli && !$mysqli->connect_errno) {
    
    // Test 2: Check Tables
    echo "<h2>2️⃣ Database Tables Test</h2>";
    $required_tables = ['users', 'products', 'auctions'];
    foreach ($required_tables as $table) {
        $result = $mysqli->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "<div class='success'>✅ Table '$table' exists</div>";
            $tests_passed++;
        } else {
            echo "<div class='error'>❌ Table '$table' NOT FOUND</div>";
            echo "<div class='warning'>💡 Solution: Run <a href='setup_complete.php'>setup_complete.php</a></div>";
            $tests_failed++;
        }
    }
    
    // Test 3: Check Users Table Structure (for Create Account)
    echo "<h2>3️⃣ Users Table Structure Test (Create Account)</h2>";
    $result = $mysqli->query("SHOW COLUMNS FROM users");
    if ($result) {
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        $required_columns = ['id', 'email', 'password_hash', 'name', 'phone', 'dob', 'gender'];
        foreach ($required_columns as $col) {
            if (in_array($col, $columns)) {
                echo "<div class='success'>✅ Column '$col' exists in users table</div>";
                $tests_passed++;
            } else {
                echo "<div class='error'>❌ Column '$col' MISSING in users table</div>";
                $tests_failed++;
            }
        }
    } else {
        echo "<div class='error'>❌ Cannot check users table structure</div>";
        $tests_failed++;
    }
    
    // Test 4: Check Products Table (for LaptopList)
    echo "<h2>4️⃣ Products Table Test (LaptopList)</h2>";
    $result = $mysqli->query("SELECT COUNT(*) as count FROM products WHERE category = 'laptop'");
    if ($result) {
        $row = $result->fetch_assoc();
        $laptop_count = $row['count'];
        if ($laptop_count > 0) {
            echo "<div class='success'>✅ Found $laptop_count laptop(s) in database</div>";
            $tests_passed++;
            
            // Show laptop details
            $result2 = $mysqli->query("SELECT id, title, base_price FROM products WHERE category = 'laptop' LIMIT 5");
            if ($result2) {
                echo "<table>";
                echo "<tr><th>ID</th><th>Title</th><th>Price</th></tr>";
                while ($row2 = $result2->fetch_assoc()) {
                    echo "<tr><td>{$row2['id']}</td><td>{$row2['title']}</td><td>₹" . number_format($row2['base_price'], 0) . "</td></tr>";
                }
                echo "</table>";
            }
        } else {
            echo "<div class='warning'>⚠️ No laptops found in database</div>";
            echo "<div class='warning'>💡 Solution: Run <a href='setup_complete.php'>setup_complete.php</a> to insert test data</div>";
            $tests_failed++;
        }
    } else {
        echo "<div class='error'>❌ Cannot check products table</div>";
        $tests_failed++;
    }
    
    // Test 5: Check Auctions Table
    echo "<h2>5️⃣ Auctions Table Test</h2>";
    $result = $mysqli->query("SELECT COUNT(*) as count FROM auctions a JOIN products p ON p.id = a.product_id WHERE p.category = 'laptop'");
    if ($result) {
        $row = $result->fetch_assoc();
        $auction_count = $row['count'];
        if ($auction_count > 0) {
            echo "<div class='success'>✅ Found $auction_count laptop auction(s) in database</div>";
            $tests_passed++;
        } else {
            echo "<div class='warning'>⚠️ No laptop auctions found</div>";
            echo "<div class='warning'>💡 Solution: Run <a href='setup_complete.php'>setup_complete.php</a></div>";
            $tests_failed++;
        }
    } else {
        echo "<div class='error'>❌ Cannot check auctions table</div>";
        $tests_failed++;
    }
    
    $mysqli->close();
}

// Test 6: API Endpoints
echo "<h2>6️⃣ API Endpoints Test</h2>";

$base_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);

// Test Register API
echo "<div class='info'>🔗 <a href='api/register.php' target='_blank'>Test Register API: api/register.php</a></div>";
echo "<div class='info'>📝 This endpoint should return JSON (even if error, means API file exists)</div>";

// Test Laptops List API
$laptops_api_url = $base_url . "/api/auctions/list.php?category=laptop";
echo "<div class='info'>🔗 <a href='api/auctions/list.php?category=laptop' target='_blank'>Test Laptops API: api/auctions/list.php?category=laptop</a></div>";
echo "<div class='info'>📝 This should return JSON with laptops array</div>";

// Test API files exist
echo "<h2>7️⃣ API Files Check</h2>";
$api_files = [
    'api/register.php' => 'Create Account registration',
    'api/auctions/list.php' => 'LaptopList data fetching',
    'db.php' => 'Database connection'
];

foreach ($api_files as $file => $purpose) {
    $file_path = __DIR__ . '/' . $file;
    if (file_exists($file_path)) {
        echo "<div class='success'>✅ File exists: $file ($purpose)</div>";
        $tests_passed++;
    } else {
        echo "<div class='error'>❌ File NOT FOUND: $file ($purpose)</div>";
        $tests_failed++;
    }
}

// Final Summary
echo "<h2>📊 Test Summary</h2>";
$total_tests = $tests_passed + $tests_failed;
$success_rate = $total_tests > 0 ? round(($tests_passed / $total_tests) * 100, 1) : 0;

if ($tests_failed == 0) {
    echo "<div class='success' style='font-size: 18px; font-weight: bold;'>";
    echo "✅ ALL TESTS PASSED! ($tests_passed/$total_tests tests passed - $success_rate%)";
    echo "</div>";
    echo "<div class='success'>";
    echo "<h3>🎉 Your backend is ready!</h3>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ol>";
    echo "<li>Make sure XAMPP Apache is running</li>";
    echo "<li>Update Android app BASE_URL in RetrofitInstance.kt if needed</li>";
    echo "<li>Build and run your Android app</li>";
    echo "<li>Test Create Account screen - should register users successfully</li>";
    echo "<li>Test LaptopList screen - should show '✅ Online' and 3 laptops</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div class='error' style='font-size: 18px; font-weight: bold;'>";
    echo "⚠️ SOME TESTS FAILED ($tests_passed/$total_tests tests passed - $success_rate%)";
    echo "</div>";
    echo "<div class='warning'>";
    echo "<h3>🔧 Fix Issues:</h3>";
    echo "<ol>";
    echo "<li>Run <a href='setup_complete.php'><strong>setup_complete.php</strong></a> to create database, tables, and insert test data</li>";
    echo "<li>Make sure XAMPP MySQL is running</li>";
    echo "<li>Check file permissions in C:\\xampp\\htdocs\\onlinebidding\\</li>";
    echo "</ol>";
    echo "</div>";
}

?>

    <h2>🔗 Quick Links</h2>
    <ul>
        <li><a href="setup_complete.php">🚀 Run Complete Setup</a></li>
        <li><a href="api/register.php">📝 Test Register API</a></li>
        <li><a href="api/auctions/list.php?category=laptop">💻 Test Laptops API</a></li>
        <li><a href="test_connection.php">🔌 Test Database Connection</a></li>
    </ul>

</body>
</html>

