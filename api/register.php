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

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["status" => false, "success" => false, "error" => "Invalid JSON data"]);
    exit;
}

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$name = $data['name'] ?? '';
$phone = $data['phone'] ?? '';
$gender = $data['gender'] ?? '';
$is_admin = isset($data['is_admin']) ? (int)$data['is_admin'] : 0; // 0 for user, 1 for admin
$is_admin = isset($data['is_admin']) ? (int)$data['is_admin'] : 0; // 0 for user, 1 for admin

// Validate required fields
if (!$email || !$password || !$name) {
    http_response_code(400);
    echo json_encode(["status" => false, "success" => false, "error" => "Missing required fields: name, email, and password are required"]);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["status" => false, "success" => false, "error" => "Invalid email format"]);
    exit;
}

// Validate password strength (minimum 8 characters, at least one letter and one number)
if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(["status" => false, "success" => false, "error" => "Password must be at least 8 characters long"]);
    exit;
}
if (!preg_match('/[a-zA-Z]/', $password)) {
    http_response_code(400);
    echo json_encode(["status" => false, "success" => false, "error" => "Password must contain at least one letter"]);
    exit;
}
if (!preg_match('/[0-9]/', $password)) {
    http_response_code(400);
    echo json_encode(["status" => false, "success" => false, "error" => "Password must contain at least one number"]);
    exit;
}

// Check if email already exists
$stmt = $mysqli->prepare("SELECT id FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    http_response_code(409);
    echo json_encode(["status" => false, "success" => false, "error" => "Email already exists. Please use a different email or login with existing account."]);
    exit;
}

// Hash password
$hash = password_hash($password, PASSWORD_BCRYPT);

// Insert user into database (without dob, with is_admin)
// Check if is_admin column exists, if not default to 0
$check_admin_column = $mysqli->query("SHOW COLUMNS FROM users LIKE 'is_admin'");
if ($check_admin_column && $check_admin_column->num_rows > 0) {
    $insert = $mysqli->prepare("INSERT INTO users (email, password_hash, name, phone, gender, is_admin) VALUES (?,?,?,?,?,?)");
    $insert->bind_param("sssssi", $email, $hash, $name, $phone, $gender, $is_admin);
} else {
    // Column doesn't exist yet, insert without it
    $insert = $mysqli->prepare("INSERT INTO users (email, password_hash, name, phone, gender) VALUES (?,?,?,?,?)");
    $insert->bind_param("sssss", $email, $hash, $name, $phone, $gender);
}

if ($insert->execute()) {
    if ($insert->affected_rows > 0) {
        http_response_code(200);
        echo json_encode([
            "status" => true,
            "success" => true,
            "message" => "Registration successful",
            "user_id" => $mysqli->insert_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => false, 
            "success" => false, 
            "error" => "Registration failed: No rows affected",
            "debug" => "Insert executed but no rows affected"
        ]);
    }
} else {
    http_response_code(500);
    echo json_encode([
        "status" => false, 
        "success" => false, 
        "error" => "Registration failed: " . $mysqli->error,
        "debug" => "Insert execution failed"
    ]);
}

$insert->close();
$mysqli->close();
?>

