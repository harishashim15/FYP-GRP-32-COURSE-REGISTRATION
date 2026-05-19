<?php
require_once __DIR__ . '/../config/database.php';

$advisor = requireRole('advisor');
$pdo = getDBConnection();

$regId = $_GET['id'] ?? null;
if (!$regId) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing registration ID']);
    exit;
}

// Fetch registration and verify it belongs to a student under this advisor
$stmt = $pdo->prepare("
    SELECT 
        cr.id,
        cr.submission_date,
        cr.status,
        cr.advisor_remarks,
        s.user_id AS student_id,
        s.user_name AS student_name,
        s.matrix_number AS matrix,
        s.programme,
        s.year,
        u.second_email,
        u.phone
    FROM course_registrations cr
    JOIN students s ON cr.student_id = s.user_id
    JOIN users u ON s.user_id = u.user_id
    WHERE cr.id = ? AND s.advisor_id = ?
");
$stmt->execute([$regId, $advisor['id']]);
$reg = $stmt->fetch();

if (!$reg) {
    http_response_code(404);
    echo json_encode(['error' => 'Registration not found or not under your supervision']);
    exit;
}

// Get semester (from student_semesters, latest)
$stmt = $pdo->prepare("
    SELECT no_semester FROM student_semesters
    WHERE student_id = ? ORDER BY no_semester DESC LIMIT 1
");
$stmt->execute([$reg['student_id']]);
$sem = $stmt->fetch();
$semesterNum = $sem ? $sem['no_semester'] : '?';

// Get registered courses
$stmt = $pdo->prepare("
    SELECT 
        rc.subject_code AS code,
        s.subject_name AS name,
        s.credits,
        rc.section
    FROM registration_courses rc
    JOIN subjects s ON rc.subject_code = s.subject_code
    WHERE rc.registration_id = ?
");
$stmt->execute([$regId]);
$courses = $stmt->fetchAll();

// Calculate total credits
$totalCredits = array_sum(array_column($courses, 'credits'));

// Advisor name for display
$advisorName = $advisor['name'] ?? 'Academic Advisor';

// Build response
$response = [
    'id' => $reg['id'],
    'student' => [
        'id' => $reg['student_id'],
        'name' => $reg['student_name'],
        'matrix' => $reg['matrix'],
        'programme' => $reg['programme'],
        'year' => $reg['year']
    ],
    'semester' => $semesterNum,
    'submission_date' => date('d M Y', strtotime($reg['submission_date'])),
    'status' => $reg['status'],
    'advisor_remarks' => $reg['advisor_remarks'],
    'courses' => $courses,
    'total_credits' => $totalCredits,
    'advisor_name' => $advisorName
];

echo json_encode($response);
?>