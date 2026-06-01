<?php
require_once __DIR__ . '/../config/database.php';

$student = requireRole('student');
$pdo = getDBConnection();

// Get unique registrations with their details
$stmt = $pdo->prepare("
    SELECT DISTINCT 
        cr.id as registration_id,
        cr.session,
        cr.submission_date,
        cr.status
    FROM course_registrations cr
    WHERE cr.student_id = ?
    ORDER BY cr.submission_date DESC
");
$stmt->execute([$student['id']]);
$registrations = $stmt->fetchAll();

// For each registration, get the courses
foreach ($registrations as &$reg) {
    $courseStmt = $pdo->prepare("
        SELECT 
            rc.subject_code as course_code,
            s.subject_name as course_name,
            rc.section
        FROM registration_courses rc
        JOIN subjects s ON rc.subject_code = s.subject_code
        WHERE rc.registration_id = ?
    ");
    $courseStmt->execute([$reg['registration_id']]);
    $reg['courses'] = $courseStmt->fetchAll();
}

echo json_encode([
    'student_name' => $student['name'],
    'registrations' => $registrations
]);
?>