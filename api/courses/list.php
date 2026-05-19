<?php
/**
 * API: Get available courses for student registration
 */

require_once __DIR__ . '/../config/database.php';

$student = requireRole('student');
$pdo = getDBConnection();

// Get all available subjects
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

// Get enrolled count for each course
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

// Get courses the student has already registered for
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