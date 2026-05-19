<?php
require_once __DIR__ . '/../config/database.php';

$student = requireRole('student');
$pdo = getDBConnection();

// All available courses
$stmt = $pdo->prepare("
    SELECT subject_code AS code, subject_name AS name, credits
    FROM subjects
    ORDER BY subject_code
");
$stmt->execute();
$courses = $stmt->fetchAll();

// Enrolled count for each course (optional)
foreach ($courses as &$c) {
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT cr.student_id) AS enrolled
        FROM registration_courses rc
        JOIN course_registrations cr ON rc.registration_id = cr.id
        WHERE rc.subject_code = ?
    ");
    $stmt->execute([$c['code']]);
    $enrolled = $stmt->fetch();
    $c['enrolled'] = (int)($enrolled['enrolled'] ?? 0);
}

// Courses already registered by this student (any status)
$stmt = $pdo->prepare("
    SELECT DISTINCT rc.subject_code AS code
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