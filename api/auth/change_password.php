<?php
/**
 * API: Change user password
 * Method: POST
 * Role: any authenticated user (advisor or student)
 * Request body: { "current_password": "...", "new_password": "..." }
 * Response: JSON success or error
 */

require_once __DIR__ . '/../config/database.php';

// Require authentication (any role)
$user = requireAuth();

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['current_password']) || !isset($input['new_password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields: current_password and new_password']);
    exit();
}

$currentPassword = $input['current_password'];
$newPassword = $input['new_password'];

// Validate new password length (at least 6 characters for students, 8 for advisors? But keep consistent)
if (strlen($newPassword) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'New password must be at least 6 characters long']);
    exit();
}

$pdo = getDBConnection();

// Fetch user's stored password
$stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$storedPassword = $stmt->fetchColumn();

// Verify current password (assumes plain text storage; update to password_hash if needed)
// Note: If your existing login uses hashed passwords, change this condition.
if ($currentPassword !== $storedPassword) {
    http_response_code(401);
    echo json_encode(['error' => 'Current password is incorrect']);
    exit();
}

// Update password (store as plain text or hash accordingly)
// If you use hashed passwords, use password_hash($newPassword, PASSWORD_DEFAULT)
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->execute([$newPassword, $user['id']]);

echo json_encode([
    'success' => true,
    'message' => 'Password changed successfully'
]);
?>