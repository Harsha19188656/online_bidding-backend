<?php
// Database connection configuration for XAMPP
$db_host = "localhost";
$db_user = "root";
$db_pass = ""; // XAMPP default has no password
$db_name = "onlinebidding";

// Try to connect to database
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

// If database doesn't exist, connect without selecting database (for setup script)
if ($mysqli->connect_errno) {
    // Try connecting without database selection
    $mysqli_temp = new mysqli($db_host, $db_user, $db_pass);
    if (!$mysqli_temp->connect_errno) {
        // Database doesn't exist, but MySQL connection works
        // This is OK for setup scripts - they will create the database
        $mysqli = $mysqli_temp;
    } else {
        // MySQL connection failed
        http_response_code(500);
        if (php_sapi_name() !== 'cli') {
            header('Content-Type: application/json');
            echo json_encode(["success" => false, "error" => "DB connection failed: " . $mysqli_temp->connect_error]);
        }
        exit;
    }
} else {
    // Connection successful, set charset
    $mysqli->set_charset("utf8mb4");
}

function require_auth($mysqli) {
    $headers = getallheaders();
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    if (!str_starts_with($auth, 'Bearer ')) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(["success" => false, "error" => "Missing token"]);
        exit;
    }
    $token = substr($auth, 7);
    $stmt = $mysqli->prepare("SELECT user_id FROM sessions WHERE token=? AND expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->bind_result($user_id);
    if ($stmt->fetch()) {
        return $user_id;
    }
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(["success" => false, "error" => "Invalid or expired token"]);
    exit;
}
?>
