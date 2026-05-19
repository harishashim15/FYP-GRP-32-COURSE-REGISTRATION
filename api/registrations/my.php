<?php
require_once __DIR__ . '/../config/database.php';

$student = requireRole('student');
$pdo = getDBConnection();

$stmt = $pdo->prepare("
    SELECT 
        cr.id AS registration_id,
        cr.submission_date,
        cr.status,
        rc.subject_code AS course_code,
        s.subject_name AS course_name,
        rc.section
    FROM course_registrations cr
    JOIN registration_courses rc ON cr.id = rc.registration_id
    JOIN subjects s ON rc.subject_code = s.subject_code
    WHERE cr.student_id = ?
    ORDER BY cr.submission_date DESC, rc.id ASC
");
$stmt->execute([$student['id']]);
$rows = $stmt->fetchAll();

$registrations = [];
foreach ($rows as $row) {
    $registrations[] = [
        'course_code' => $row['course_code'],
        'course_name' => $row['course_name'],
        'registration_date' => date('d M Y', strtotime($row['submission_date'])),
        'status' => $row['status']
    ];
}

echo json_encode([
    'student_name' => $student['name'],
    'registrations' => $registrations
]);
?>