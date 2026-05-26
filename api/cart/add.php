<?php
require_once __DIR__ . '/../config/database.php';

$student = requireRole('student');
$pdo = getDBConnection();

$input = json_decode(file_get_contents('php://input'), true);
$courseCode = $input['course_code'] ?? '';

if (!$courseCode) {
    http_response_code(400);
    echo json_encode(['error' => 'Course code required']);
    exit;
}

// Check if already in cart
$stmt = $pdo->prepare("SELECT id FROM registration_cart WHERE student_id = ? AND subject_code = ?");
$stmt->execute([$student['id'], $courseCode]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['error' => 'Course already in cart']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO registration_cart (student_id, subject_code, added_date) VALUES (?, ?, NOW())");
$stmt->execute([$student['id'], $courseCode]);

echo json_encode(['success' => true]);
?>