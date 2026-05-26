<?php
require_once __DIR__ . '/../config/database.php';

$student = requireRole('student');
$pdo = getDBConnection();

$input = json_decode(file_get_contents('php://input'), true);
$courseCode = $input['course_code'] ?? '';

if (!$courseCode) {
    http_response_code(400);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM registration_cart WHERE student_id = ? AND subject_code = ?");
$stmt->execute([$student['id'], $courseCode]);

echo json_encode(['success' => true]);
?>