<?php
/**
 * API: Get list of student registrations for advisor
 * Method: GET
 * Role: advisor
 * Parameters: status (optional: 'pending', 'approved', 'rejected', 'all')
 *            limit (optional: default 100)
 * Response: JSON array of registration objects
 */

require_once __DIR__ . '/../config/database.php';

// Only advisors can access
$advisor = requireRole('advisor');

// Get optional parameters
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;

$pdo = getDBConnection();

// Build query to get registrations for students under this advisor
$sql = "
    SELECT 
        cr.id,
        cr.student_id,
        cr.submission_date,
        cr.status,
        cr.advisor_remarks,
        cr.reviewed_at,
        u.name as student_name,
        u.Matrix as student_id,
        COUNT(rc.id) as course_count
    FROM course_registrations cr
    JOIN users u ON cr.student_id = u.id
    LEFT JOIN registration_courses rc ON cr.id = rc.registration_id
    WHERE u.advisor_id = :advisor_id
";

if ($status !== 'all') {
    $sql .= " AND cr.status = :status";
}

$sql .= " GROUP BY cr.id ORDER BY cr.submission_date DESC LIMIT :limit";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':advisor_id', $advisor['id'], PDO::PARAM_INT);
if ($status !== 'all') {
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
}
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();

$registrations = $stmt->fetchAll();

// Format dates
foreach ($registrations as &$reg) {
    $reg['submission_date'] = date('d M Y', strtotime($reg['submission_date']));
    if ($reg['reviewed_at']) {
        $reg['reviewed_at'] = date('d M Y H:i', strtotime($reg['reviewed_at']));
    }
    $reg['course_count'] = (int)$reg['course_count'];
}

echo json_encode($registrations);
?>