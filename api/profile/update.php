<?php
require_once __DIR__ . '/../config/database.php';

$user = requireAuth();

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON input']);
    exit();
}

$fullName = $input['full_name'] ?? null;
$utmEmail = $input['utm_email'] ?? null;
$secondEmail = $input['second_email'] ?? null;
$phone = $input['phone'] ?? null;

if (!$fullName || !$utmEmail) {
    http_response_code(400);
    echo json_encode(['error' => 'Full name and UTM email are required']);
    exit();
}

$pdo = getDBConnection();

$stmt = $pdo->prepare("
    UPDATE users 
    SET user_name = ?, utm_email = ?, second_email = ?, phone = ?
    WHERE user_id = ?
");
$stmt->execute([$fullName, $utmEmail, $secondEmail, $phone, $user['id']]);

echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
?>