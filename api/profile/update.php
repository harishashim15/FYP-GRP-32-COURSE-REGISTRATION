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

$updates = [];
$params = [];

// Allowed editable fields
if (isset($input['second_email'])) {
    $updates[] = "second_email = ?";
    $params[] = trim($input['second_email']);
}
if (isset($input['phone'])) {
    $updates[] = "phone = ?";
    $params[] = trim($input['phone']);
}

// If department is provided, update the advisor table (for advisors only)
if (isset($input['department']) && $user['role'] === 'advisor') {
    $stmt = $pdo->prepare("UPDATE advisor SET department = ? WHERE user_id = ?");
    $stmt->execute([trim($input['department']), $user['id']]);
}

// If there are fields to update in users table
if (!empty($updates)) {
    $params[] = $user['id'];
    $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}

echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
?>