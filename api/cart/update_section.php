<?php
require_once __DIR__ . '/../config/database.php';

$student = requireRole('student');
$pdo = getDBConnection();

$input = json_decode(file_get_contents('php://input'), true);
$courseCode = $input['course_code'] ?? '';
$section = $input['section'] ?? '';

if (!$courseCode) {
    http_response_code(400);
    exit;
}

$stmt = $pdo->prepare("UPDATE registration_cart SET section = ? WHERE student_id = ? AND subject_code = ?");
$stmt->execute([$section, $student['id'], $courseCode]);

echo json_encode(['success' => true]);
?>