<?php
/**
 * API: Get available courses for student registration
 * Method: GET
 * Role: student
 * Response: JSON with student name, courses list, and already registered course codes
 */

require_once __DIR__ . '/../config/database.php';

// Only students can access
$student = requireRole('student');

$pdo = getDBConnection();

// Get all available subjects (courses)
$stmt = $pdo->prepare("
    SELECT 
        subject_code as code,
        subject_name as name,
        credits
    FROM subjects
    ORDER BY subject_code ASC
");
$stmt->execute();
$courses = $stmt->fetchAll();

// For each course, get enrolled count (total distinct students who have registered for this course, any status)
// This is optional but nice for display
foreach ($courses as &$course) {
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT cr.student_id) as enrolled
        FROM registration_courses rc
        JOIN course_registrations cr ON rc.registration_id = cr.id
        WHERE rc.subject_code = ?
    ");
    $stmt->execute([$course['code']]);
    $enrolled = $stmt->fetch();
    $course['enrolled'] = (int)$enrolled['enrolled'];
}

// Get the list of course codes the student has already registered for (any status)
$stmt = $pdo->prepare("
    SELECT DISTINCT rc.subject_code as code
    FROM registration_courses rc
    JOIN course_registrations cr ON rc.registration_id = cr.id
    WHERE cr.student_id = ?
");
$stmt->execute([$student['id']]);
$registeredRows = $stmt->fetchAll();
$registeredCodes = array_column($registeredRows, 'code');

echo json_encode([
    'student_name' => $student['name'],
    'courses' => $courses,
    'registered_codes' => $registeredCodes
]);
?>