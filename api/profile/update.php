<?php
require_once __DIR__ . '/../config/database.php';

$user = requireAuth();
$pdo = getDBConnection();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

// Prepare dynamic update query
$updates = [];
$params = [];

if (isset($input['second_email'])) {
    $updates[] = "second_email = ?";
    $params[] = trim($input['second_email']);
}
if (isset($input['phone'])) {
    $updates[] = "phone = ?";
    $params[] = trim($input['phone']);
}
// Optional: allow full_name and utm_email if needed later, but frontend disabled them
if (isset($input['full_name'])) {
    $updates[] = "user_name = ?";
    $params[] = trim($input['full_name']);
}
if (isset($input['utm_email'])) {
    $updates[] = "utm_email = ?";
    $params[] = trim($input['utm_email']);
}

if (empty($updates)) {
    http_response_code(400);
    echo json_encode(['error' => 'No fields to update']);
    exit;
}

$params[] = $user['id'];
$sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
?>