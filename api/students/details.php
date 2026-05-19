<?php
require_once __DIR__ . '/../config/database.php';

$advisor = requireRole('advisor');
$pdo = getDBConnection();

$studentId = $_GET['id'] ?? null;
if (!$studentId || !is_numeric($studentId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid student ID']);
    exit;
}

// Verify that the student belongs to this advisor (via students table)
$stmt = $pdo->prepare("
    SELECT s.user_id, s.user_name, s.matrix_number, s.programme, s.year, s.utm_email,
           u.second_email, u.phone, u.role
    FROM students s
    JOIN users u ON s.user_id = u.user_id
    WHERE s.user_id = ? AND s.advisor_id = ?
");
$stmt->execute([$studentId, $advisor['id']]);
$student = $stmt->fetch();

if (!$student) {
    http_response_code(404);
    echo json_encode(['error' => 'Student not found or not under your supervision']);
    exit;
}

// Get latest registration status and registration ID
$stmt = $pdo->prepare("
    SELECT id, status FROM course_registrations
    WHERE student_id = ?
    ORDER BY id DESC LIMIT 1
");
$stmt->execute([$studentId]);
$reg = $stmt->fetch();

$registration_status = $reg ? $reg['status'] : 'pending';
$active_registration_id = $reg ? $reg['id'] : null;

// Get current semester info from student_semesters
$stmt = $pdo->prepare("
    SELECT session_semester, programme, active_code
    FROM student_semesters
    WHERE student_id = ?
    ORDER BY no_semester DESC LIMIT 1
");
$stmt->execute([$studentId]);
$sem = $stmt->fetch();

$current_semester = $sem ? $sem['session_semester'] : '--';
$year_programme = $sem ? $sem['programme'] : '--';
$active_code = $sem ? $sem['active_code'] : 'A - Active';

// Prepare response
echo json_encode([
    'id' => $student['user_id'],
    'name' => $student['user_name'],
    'email' => $student['utm_email'],
    'matrix' => $student['matrix_number'],
    'programme' => $student['programme'],
    'year' => $student['year'],
    'advisor_id' => $advisor['id'],
    'advisor_name' => $advisor['name'],
    'registration_status' => $registration_status,
    'active_registration_id' => $active_registration_id,
    'current_semester' => $current_semester,
    'year_programme' => $year_programme,
    'active_code' => $active_code
]);
?>