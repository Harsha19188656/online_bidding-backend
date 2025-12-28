<?php
// Helper function to get authorization token from various sources
function getAuthToken() {
    // Method 1: Direct HTTP_AUTHORIZATION (works in some servers)
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        return str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
    }
    
    // Method 2: getallheaders() function (Apache)
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            return str_replace('Bearer ', '', $headers['Authorization']);
        }
        if (isset($headers['authorization'])) {
            return str_replace('Bearer ', '', $headers['authorization']);
        }
    }
    
    // Method 3: Apache redirect
    if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        return str_replace('Bearer ', '', $_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
    }
    
    // Method 4: Try to parse from php://input or query string as fallback
    // For testing purposes only - should use headers in production
    if (isset($_GET['token'])) {
        return $_GET['token'];
    }
    
    return null;
}

function verifyAdminToken($mysqli, $token) {
    if (!$token) {
        return null;
    }
    
    $stmt = $mysqli->prepare("SELECT u.id, u.is_admin FROM users u INNER JOIN sessions s ON u.id = s.user_id WHERE s.token = ? AND s.expires_at > NOW()");
    if (!$stmt) {
        return null;
    }
    
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if ($user && $user['is_admin'] == 1) {
        return $user;
    }
    
    return null;
}
?>

