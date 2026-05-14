<?php
/**
 * API: Register a student for a course
 * Method: POST
 * Role: student
 * Request body: { "course_code": "SECJ2154" }
 * Response: JSON success or error
 */

require_once __DIR__ . '/../config/database.php';

// Only students can access
$student = requireRole('student');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['course_code'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing course_code field']);
    exit();
}

$courseCode = trim($input['course_code']);

$pdo = getDBConnection();

// Verify the course exists in subjects table
$stmt = $pdo->prepare("SELECT subject_code, subject_name FROM subjects WHERE subject_code = ?");
$stmt->execute([$courseCode]);
$course = $stmt->fetch();

if (!$course) {
    http_response_code(404);
    echo json_encode(['error' => 'Course not found']);
    exit();
}

// Check if the student has already registered for this course in any pending/approved registration
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
    echo json_encode(['error' => 'You have already registered for this course (status: ' . $existing['status'] . ')']);
    exit();
}

// Find if there is a pending registration for this student (to add course to it)
$stmt = $pdo->prepare("
    SELECT id FROM course_registrations 
    WHERE student_id = ? AND status = 'pending'
    ORDER BY id DESC LIMIT 1
");
$stmt->execute([$student['id']]);
$pendingReg = $stmt->fetch();

if ($pendingReg) {
    // Add course to existing pending registration
    $registrationId = $pendingReg['id'];
} else {
    // Create new registration record
    $stmt = $pdo->prepare("
        INSERT INTO course_registrations (student_id, submission_date, status) 
        VALUES (?, CURDATE(), 'pending')
    ");
    $stmt->execute([$student['id']]);
    $registrationId = $pdo->lastInsertId();
}

// Insert the registration_courses link
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