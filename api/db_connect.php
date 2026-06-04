<?php
/**
 * Database connection file for API endpoints
 * Located at: api/db_connect.php
 */

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'fypdb3';

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_password, $db_name);

// Check connection
if (!$conn) {
    // Return JSON error instead of dying (since this is for API)
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . mysqli_connect_error()]);
    exit();
}

// Set charset to UTF-8
mysqli_set_charset($conn, 'utf8mb4');

// Note: Do NOT close the connection here - it will be used by the API files
?>