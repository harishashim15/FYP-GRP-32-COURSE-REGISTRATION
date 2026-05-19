<?php
/**
 * API: Register a student for a course
 */

require_once __DIR__ . '/../config/database.php';

$student = requireRole('student');
$pdo = getDBConnection();

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['course_code'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing course_code field']);
    exit();
}

$courseCode = trim($input['course_code']);

// Verify course exists
$stmt = $pdo->prepare("SELECT subject_code, subject_name FROM subjects WHERE subject_code = ?");
$stmt->execute([$courseCode]);
$course = $stmt->fetch();

if (!$course) {
    http_response_code(404);
    echo json_encode(['error' => 'Course not found']);
    exit();
}

// Check if already registered
$stmt = $pdo->prepare("
    SELECT cr.status 
    FROM registration_courses rc
    JOIN course_registrations cr ON rc.registration_id = cr.id
    WHERE cr.student_id = ? AND rc.subject_code = ?
    LIMIT 1
");
$stmt->execute([$student['id'], $courseCode]);
$existing = $stmt->fetch();

if ($existing) {
    http_response_code(409);
    echo json_encode(['error' => 'You have already registered for this course']);
    exit();
}

// Find pending registration or create new one
$stmt = $pdo->prepare("
    SELECT id FROM course_registrations 
    WHERE student_id = ? AND status = 'pending'
    ORDER BY id DESC LIMIT 1
");
$stmt->execute([$student['id']]);
$pendingReg = $stmt->fetch();

if ($pendingReg) {
    $registrationId = $pendingReg['id'];
} else {
    $stmt = $pdo->prepare("
        INSERT INTO course_registrations (student_id, submission_date, status) 
        VALUES (?, CURDATE(), 'pending')
    ");
    $stmt->execute([$student['id']]);
    $registrationId = $pdo->lastInsertId();
}

// Add course to registration
$stmt = $pdo->prepare("
    INSERT INTO registration_courses (registration_id, subject_code, section) 
    VALUES (?, ?, 'A')
");
$stmt->execute([$registrationId, $courseCode]);

echo json_encode([
    'success' => true,
    'message' => 'Successfully registered for ' . $course['subject_name'],
    'registration_id' => $registrationId
]);
?>