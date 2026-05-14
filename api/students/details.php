<?php
/**
 * API: Get student details by ID (for advisor)
 * Method: GET
 * Role: advisor
 * Parameters: id (student ID)
 * Response: JSON with student details
 */

require_once __DIR__ . '/../config/database.php';

// Only advisors can access
$advisor = requireRole('advisor');

// Validate student ID parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid student ID']);
    exit();
}

$studentId = (int)$_GET['id'];

$pdo = getDBConnection();

// Verify this student belongs to the current advisor
$stmt = $pdo->prepare("
    SELECT 
        u.id,
        u.name,
        u.email,
        u.Matrix as matrix,
        u.programme,
        u.year,
        u.phone,
        u.advisor_id
    FROM users u
    WHERE u.id = ? AND u.role = 'student' AND u.advisor_id = ?
");
$stmt->execute([$studentId, $advisor['id']]);
$student = $stmt->fetch();

if (!$student) {
    http_response_code(404);
    echo json_encode(['error' => 'Student not found or not assigned to you']);
    exit();
}

// Get current registration status and active registration ID
$stmt = $pdo->prepare("
    SELECT id, status 
    FROM course_registrations 
    WHERE student_id = ? 
    ORDER BY id DESC 
    LIMIT 1
");
$stmt->execute([$studentId]);
$registration = $stmt->fetch();

$status = $registration ? $registration['status'] : 'pending';
$regId = $registration ? $registration['id'] : null;

// Get current semester info (from student_semesters)
$stmt = $pdo->prepare("
    SELECT session_semester, programme as year_programme, active_code
    FROM student_semesters 
    WHERE student_id = ? 
    ORDER BY no_semester DESC 
    LIMIT 1
");
$stmt->execute([$studentId]);
$semesterInfo = $stmt->fetch();

echo json_encode([
    'id' => $student['id'],
    'name' => $student['name'],
    'email' => $student['email'],
    'matrix' => $student['matrix'],
    'programme' => $student['programme'],
    'year' => $student['year'],
    'phone' => $student['phone'],
    'advisor_id' => $student['advisor_id'],
    'advisor_name' => $advisor['name'],
    'registration_status' => $status,
    'active_registration_id' => $regId,
    'current_semester' => $semesterInfo ? $semesterInfo['session_semester'] : '--',
    'year_programme' => $semesterInfo ? $semesterInfo['year_programme'] : '--',
    'active_code' => $semesterInfo ? $semesterInfo['active_code'] : 'A - Active'
]);
?>