<?php
// Test database connection
header('Content-Type: application/json');

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "onlinebidding";

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_errno) {
    echo json_encode([
        "success" => false,
        "error" => "Database connection failed: " . $mysqli->connect_error,
        "details" => [
            "host" => $db_host,
            "user" => $db_user,
            "database" => $db_name
        ]
    ]);
} else {
    // Check if users table exists
    $result = $mysqli->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Database connection successful",
            "table_exists" => true
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => "Database connected but 'users' table does not exist. Please run schema.sql",
            "database" => $db_name
        ]);
    }
    $mysqli->close();
}
?>

