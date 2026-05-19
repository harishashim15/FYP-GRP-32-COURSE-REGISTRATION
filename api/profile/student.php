<?php
require_once __DIR__ . '/../config/database.php';

$student = requireRole('student');
$pdo = getDBConnection();

$stmt = $pdo->prepare("
    SELECT 
        user_name as full_name,
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
    exit();
}

echo json_encode($profile);
?>