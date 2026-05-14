<?php
/**
 * API: Get registration details by ID (for advisor verification)
 * Method: GET
 * Role: advisor
 * Parameters: id (registration ID)
 * Response: JSON with registration details, student info, and courses
 */

require_once __DIR__ . '/../config/database.php';

// Only advisors can access
$advisor = requireRole('advisor');

// Validate registration ID parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid registration ID']);
    exit();
}

$regId = (int)$_GET['id'];

$pdo = getDBConnection();

// Fetch registration and verify it belongs to a student under this advisor
$stmt = $pdo->prepare("
    SELECT 
        cr.id,
        cr.student_id,
        cr.submission_date,
        cr.status,
        cr.advisor_remarks,
        u.name as student_name,
        u.Matrix as student_matrix,
        u.programme,
        u.year,
        u.email as student_email
    FROM course_registrations cr
    JOIN users u ON cr.student_id = u.id
    WHERE cr.id = ? AND u.advisor_id = ?
");
$stmt->execute([$regId, $advisor['id']]);
$registration = $stmt->fetch();

if (!$registration) {
    http_response_code(404);
    echo json_encode(['error' => 'Registration not found or not under your supervision']);
    exit();
}

// Get semester from latest student_semester record (optional)
$stmt = $pdo->prepare("
    SELECT no_semester 
    FROM student_semesters 
    WHERE student_id = ? 
    ORDER BY no_semester DESC 
    LIMIT 1
");
$stmt->execute([$registration['student_id']]);
$semester = $stmt->fetch();
$semesterNum = $semester ? $semester['no_semester'] : '?';

// Get registered courses
$stmt = $pdo->prepare("
    SELECT 
        rc.subject_code as code,
        s.subject_name as name,
        s.credits,
        rc.section
    FROM registration_courses rc
    JOIN subjects s ON rc.subject_code = s.subject_code
    WHERE rc.registration_id = ?
");
$stmt->execute([$regId]);
$courses = $stmt->fetchAll();

// Calculate total credits
$totalCredits = 0;
foreach ($courses as $course) {
    $totalCredits += (int)$course['credits'];
}

echo json_encode([
    'id' => $registration['id'],
    'student' => [
        'id' => $registration['student_id'],
        'name' => $registration['student_name'],
        'matrix' => $registration['student_matrix'],
        'programme' => $registration['programme'],
        'year' => $registration['year'],
        'email' => $registration['student_email'],
        'initials' => implode('', array_map(function($n) { return $n[0]; }, explode(' ', $registration['student_name'])))
    ],
    'semester' => $semesterNum,
    'submission_date' => date('d M Y', strtotime($registration['submission_date'])),
    'status' => $registration['status'],
    'advisor_remarks' => $registration['advisor_remarks'],
    'courses' => $courses,
    'total_credits' => $totalCredits,
    'advisor_name' => $advisor['name']
]);
?>