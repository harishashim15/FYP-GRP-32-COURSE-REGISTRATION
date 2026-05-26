<?php
require_once __DIR__ . '/../config/database.php';

$advisor = requireRole('advisor');
$pdo = getDBConnection();

$status = $_GET['status'] ?? 'all';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;

$sql = "
    SELECT 
        cr.id,
        s.user_name AS student_name,
        s.matrix_number AS student_id,
        COUNT(rc.id) AS course_count,
        cr.submission_date,
        cr.status
    FROM course_registrations cr
    JOIN students s ON cr.student_id = s.user_id
    LEFT JOIN registration_courses rc ON cr.id = rc.registration_id
    WHERE s.advisor_id = :advisor_id
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
    $reg['course_count'] = (int)$reg['course_count'];
}

echo json_encode($registrations);
?>