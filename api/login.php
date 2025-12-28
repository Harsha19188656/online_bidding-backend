<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors, but log them

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require __DIR__ . '/../db.php';

// Check if database connection was successful
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database connection failed: " . $mysqli->connect_error]);
    exit;
}

// Make sure we're using the correct database
if (!$mysqli->select_db("onlinebidding")) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database selection failed"]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid JSON data"]);
    exit;
}

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Email and password are required"]);
    exit;
}

// Check if email exists - select basic fields first
$stmt = $mysqli->prepare("SELECT id, password_hash, name, phone FROM users WHERE email=?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database query preparation failed: " . $mysqli->error]);
    exit;
}

$stmt->bind_param("s", $email);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database query execution failed: " . $stmt->error]);
    $stmt->close();
    exit;
}

$stmt->bind_result($uid, $hash, $name, $phone);
$userFound = $stmt->fetch();
$stmt->close();

// Try to get is_admin separately (in case column doesn't exist)
$is_admin = 0;
if ($userFound && $uid) {
    // Check if is_admin column exists by trying to select it
    $check_column = $mysqli->query("SHOW COLUMNS FROM users LIKE 'is_admin'");
    if ($check_column && $check_column->num_rows > 0) {
        $admin_stmt = $mysqli->prepare("SELECT is_admin FROM users WHERE id = ?");
        if ($admin_stmt) {
            $admin_stmt->bind_param("i", $uid);
            if ($admin_stmt->execute()) {
                $admin_result = $admin_stmt->get_result();
                if ($admin_result && $admin_row = $admin_result->fetch_assoc()) {
                    $is_admin = isset($admin_row['is_admin']) ? (int)$admin_row['is_admin'] : 0;
                }
            }
            $admin_stmt->close();
        }
    }
    // If column doesn't exist, is_admin remains 0 (default)
}

if ($userFound) {
    // Email exists, verify password
    if (password_verify($password, $hash)) {
        // Password is correct, create session
        $token = bin2hex(random_bytes(32));
        $exp = date('Y-m-d H:i:s', time() + 86400);
        
        $insert = $mysqli->prepare("INSERT INTO sessions (user_id, token, expires_at) VALUES (?,?,?)");
        if (!$insert) {
            http_response_code(500);
            echo json_encode(["success" => false, "error" => "Session creation failed: " . $mysqli->error]);
            exit;
        }
        
        $insert->bind_param("iss", $uid, $token, $exp);
        if ($insert->execute()) {
            http_response_code(200);
            $role = ($is_admin == 1) ? "admin" : "user";
            echo json_encode(["success" => true, "token" => $token, "user_id" => $uid, "name" => $name, "email" => $email, "phone" => $phone, "role" => $role]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "error" => "Session creation failed: " . $insert->error]);
        }
        $insert->close();
    } else {
        // Email exists but password is wrong
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "Incorrect password"]);
    }
} else {
    // Email does not exist
    http_response_code(404);
    echo json_encode(["success" => false, "error" => "Email does not exist. Please create an account to login."]);
}

$mysqli->close();
?>

