<?php
require_once __DIR__ . '/../config/database.php';

$student = requireRole('student');
$pdo = getDBConnection();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['course_code'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing course_code']);
    exit;
}

$courseCode = $input['course_code'];

// Verify course exists
$stmt = $pdo->prepare("SELECT subject_name FROM subjects WHERE subject_code = ?");
$stmt->execute([$courseCode]);
$course = $stmt->fetch();
if (!$course) {
    http_response_code(404);
    echo json_encode(['error' => 'Course not found']);
    exit;
}

// Check if already registered (any status)
$stmt = $pdo->prepare("
    SELECT 1 FROM registration_courses rc
    JOIN course_registrations cr ON rc.registration_id = cr.id
    WHERE cr.student_id = ? AND rc.subject_code = ?
    LIMIT 1
");
$stmt->execute([$student['id'], $courseCode]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['error' => 'Already registered for this course']);
    exit;
}

// Find existing pending registration or create new one
$stmt = $pdo->prepare("
    SELECT id FROM course_registrations
    WHERE student_id = ? AND status = 'pending'
    ORDER BY id DESC LIMIT 1
");
$stmt->execute([$student['id']]);
$pending = $stmt->fetch();

if ($pending) {
    $registrationId = $pending['id'];
} else {
    $stmt = $pdo->prepare("
        INSERT INTO course_registrations (student_id, submission_date, status)
        VALUES (?, CURDATE(), 'pending')
    ");
    $stmt->execute([$student['id']]);
    $registrationId = $pdo->lastInsertId();
}

// Insert registration course link
$stmt = $pdo->prepare("
    INSERT INTO registration_courses (registration_id, subject_code, section)
    VALUES (?, ?, 'A')
");
$stmt->execute([$registrationId, $courseCode]);

echo json_encode([
    'success' => true,
    'message' => 'Registration successful for ' . $course['subject_name']
]);
?>