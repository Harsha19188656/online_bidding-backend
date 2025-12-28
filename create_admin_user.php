<?php
// Script to create an admin user
// Run this once: http://localhost/onlinebidding/create_admin_user.php

require __DIR__ . '/db.php';

if ($mysqli->connect_errno) {
    die("Database connection failed: " . $mysqli->connect_error);
}

if (!$mysqli->select_db("onlinebidding")) {
    die("Database selection failed");
}

// Add is_admin column if it doesn't exist
$mysqli->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS is_admin TINYINT(1) DEFAULT 0");

// Create admin user
$admin_email = "admin@gmail.com";
$admin_password = "admin@123";
$admin_name = "Admin User";
$password_hash = password_hash($admin_password, PASSWORD_BCRYPT);

// Check if admin already exists
$stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $admin_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update existing user to admin
    $update = $mysqli->prepare("UPDATE users SET is_admin = 1, password_hash = ? WHERE email = ?");
    $update->bind_param("ss", $password_hash, $admin_email);
    if ($update->execute()) {
        echo "✅ Admin user updated!<br>";
        echo "Email: $admin_email<br>";
        echo "Password: $admin_password<br>";
    } else {
        echo "❌ Failed to update admin user: " . $update->error;
    }
    $update->close();
} else {
    // Create new admin user
    $insert = $mysqli->prepare("INSERT INTO users (email, password_hash, name, is_admin) VALUES (?, ?, ?, 1)");
    $insert->bind_param("sss", $admin_email, $password_hash, $admin_name);
    if ($insert->execute()) {
        echo "✅ Admin user created!<br>";
        echo "Email: $admin_email<br>";
        echo "Password: $admin_password<br>";
    } else {
        echo "❌ Failed to create admin user: " . $insert->error;
    }
    $insert->close();
}

$stmt->close();
$mysqli->close();
?>

