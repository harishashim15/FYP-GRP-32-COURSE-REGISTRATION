<?php
/**
 * API: Change user password
 * Method: POST
 * Role: any authenticated user (student/advisor)
 * Request body: { "current_password": "...", "new_password": "..." }
 * Response: JSON success or error
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated. Please login again.']);
    exit();
}

// Include database connection
require_once __DIR__ . '/../db_connect.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['current_password']) || !isset($input['new_password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields: current_password and new_password']);
    exit();
}

$currentPassword = $input['current_password'];
$newPassword = $input['new_password'];
$user_id = $_SESSION['user_id'];

// Validate new password length
if (strlen($newPassword) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'New password must be at least 6 characters long']);
    exit();
}

// Fetch user's stored password
$query = "SELECT password FROM users WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'User not found']);
    exit();
}

$storedPassword = $user['password'];

// Verify current password (plain text comparison - matches your login system)
if ($currentPassword !== $storedPassword) {
    http_response_code(401);
    echo json_encode(['error' => 'Current password is incorrect']);
    exit();
}

// Update password
$updateQuery = "UPDATE users SET password = '$newPassword' WHERE user_id = '$user_id'";
if (mysqli_query($conn, $updateQuery)) {
    echo json_encode([
        'success' => true,
        'message' => 'Password changed successfully'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . mysqli_error($conn)]);
}
?>