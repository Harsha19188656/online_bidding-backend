<?php
// Database configuration for XAMPP
$host = 'localhost';
$username = 'root';
$password = ''; // Default XAMPP MySQL password is empty
$database = 'onlinebidding';

// Create connection
$mysqli = new mysqli($host, $username, $password, $database);

// Check connection
if ($mysqli->connect_error) {
    error_log("Database connection failed: " . $mysqli->connect_error);
    // Don't output error to client, let the calling script handle it
}

// Set charset to utf8mb4 for proper character encoding
$mysqli->set_charset("utf8mb4");
