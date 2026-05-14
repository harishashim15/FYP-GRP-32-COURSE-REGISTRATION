<?php
/**
 * API: Get student semester history (CPA, programme, etc.)
 * Method: GET
 * Role: advisor
 * Parameters: id (student ID)
 * Response: JSON array of semester records
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
    SELECT id FROM users 
    WHERE id = ? AND role = 'student' AND advisor_id = ?
");
$stmt->execute([$studentId, $advisor['id']]);
$student = $stmt->fetch();

if (!$student) {
    http_response_code(404);
    echo json_encode(['error' => 'Student not found or not assigned to you']);
    exit();
}

// Fetch semester history
$stmt = $pdo->prepare("
    SELECT 
        session_semester as session,
        programme,
        no_semester as noSem,
        DATE_FORMAT(reg_date, '%d %b %Y') as regDate,
        active_code as activeCode,
        cpa
    FROM student_semesters
    WHERE student_id = ?
    ORDER BY no_semester ASC
");
$stmt->execute([$studentId]);
$semesters = $stmt->fetchAll();

// Ensure CPA is formatted as string with 2 decimals if not null
foreach ($semesters as &$sem) {
    if ($sem['cpa'] !== null) {
        $sem['cpa'] = number_format((float)$sem['cpa'], 2);
    }
}

echo json_encode($semesters);
?>