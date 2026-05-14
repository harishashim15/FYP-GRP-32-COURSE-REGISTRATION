<?php
/**
 * API: Update user profile (name, email, phone)
 * Method: POST
 * Role: any authenticated user (advisor or student)
 * Request body: { "full_name": "...", "email": "...", "phone": "..." }
 * Response: JSON success or error
 */

require_once __DIR__ . '/../config/database.php';

// Require authentication (any role)
$user = requireAuth();

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON input']);
    exit();
}

// Validate required fields
$fullName = isset($input['full_name']) ? trim($input['full_name']) : null;
$email = isset($input['email']) ? trim($input['email']) : null;
$phone = isset($input['phone']) ? trim($input['phone']) : null;

if (!$fullName || !$email) {
    http_response_code(400);
    echo json_encode(['error' => 'Full name and email are required']);
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit();
}

$pdo = getDBConnection();

// Check if email is already used by another user (excluding current user)
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$stmt->execute([$email, $user['id']]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['error' => 'Email already in use by another account']);
    exit();
}

// Update user profile
$stmt = $pdo->prepare("
    UPDATE users 
    SET name = ?, email = ?, phone = ?
    WHERE id = ?
");
$stmt->execute([$fullName, $email, $phone, $user['id']]);

echo json_encode([
    'success' => true,
    'message' => 'Profile updated successfully'
]);
?>