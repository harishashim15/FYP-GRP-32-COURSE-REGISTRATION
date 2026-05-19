<?php
/**
 * API: Get current student's own registrations
 */

require_once __DIR__ . '/../config/database.php';

$student = requireRole('student');
$pdo = getDBConnection();

$stmt = $pdo->prepare("
    SELECT 
        cr.id as registration_id,
        cr.submission_date,
        cr.status,
        rc.subject_code as course_code,
        s.subject_name as course_name,
        rc.section
    FROM course_registrations cr
    LEFT JOIN registration_courses rc ON cr.id = rc.registration_id
    LEFT JOIN subjects s ON rc.subject_code = s.subject_code
    WHERE cr.student_id = ?
    ORDER BY cr.submission_date DESC, rc.id ASC
");
$stmt->execute([$student['id']]);
$rows = $stmt->fetchAll();

$registrations = [];
foreach ($rows as $row) {
    if ($row['course_code']) {
        $registrations[] = [
            'registration_id' => $row['registration_id'],
            'course_code' => $row['course_code'],
            'course_name' => $row['course_name'],
            'section' => $row['section'],
            'registration_date' => date('d M Y', strtotime($row['submission_date'])),
            'status' => $row['status']
        ];
    }
}

echo json_encode([
    'student_name' => $student['name'],
    'registrations' => $registrations
]);
?>