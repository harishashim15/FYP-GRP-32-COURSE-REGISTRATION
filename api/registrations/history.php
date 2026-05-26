<?php
require_once __DIR__ . '/../config/database.php';

$student = requireRole('student');
$pdo = getDBConnection();

$stmt = $pdo->prepare("
    SELECT 
        cr.id,
        rc.subject_code as course_code,
        s.subject_name as course_name,
        cr.section,
        cr.session,
        cr.submission_date,
        cr.status
    FROM course_registrations cr
    LEFT JOIN registration_courses rc ON cr.id = rc.registration_id
    LEFT JOIN subjects s ON rc.subject_code = s.subject_code
    WHERE cr.student_id = ?
    ORDER BY cr.submission_date DESC
");
$stmt->execute([$student['id']]);
$registrations = $stmt->fetchAll();

echo json_encode([
    'student_name' => $student['name'],
    'registrations' => $registrations
]);
?>