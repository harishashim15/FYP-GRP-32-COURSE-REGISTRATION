<?php
require_once __DIR__ . '/../config/database.php';

$student = requireRole('student');
$pdo = getDBConnection();

$stmt = $pdo->prepare("
    SELECT 
        user_name AS full_name,
        matrix_number,
        utm_email,
        second_email,
        phone,
        role
    FROM users
    WHERE user_id = ?
");
$stmt->execute([$student['id']]);
$profile = $stmt->fetch();

if (!$profile) {
    http_response_code(404);
    echo json_encode(['error' => 'Profile not found']);
    exit;
}

// Ensure all fields exist
$profile['full_name'] = $profile['full_name'] ?? '';
$profile['matrix_number'] = $profile['matrix_number'] ?? '';
$profile['utm_email'] = $profile['utm_email'] ?? '';
$profile['second_email'] = $profile['second_email'] ?? '';
$profile['phone'] = $profile['phone'] ?? '';
$profile['role'] = $profile['role'] ?? 'student';

echo json_encode($profile);
?>