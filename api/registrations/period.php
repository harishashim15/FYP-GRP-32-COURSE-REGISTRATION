<?php
/**
 * API: Get current registration period
 */

require_once __DIR__ . '/../config/database.php';

$user = requireAuth();
$pdo = getDBConnection();

$stmt = $pdo->prepare("
    SELECT session_semester, start_date, end_date, is_open
    FROM semester_registration_periods
    ORDER BY is_open DESC, start_date DESC
    LIMIT 1
");
$stmt->execute();
$period = $stmt->fetch();

if (!$period) {
    echo json_encode([
        'is_open' => true,
        'start_date' => '1 May 2026',
        'end_date' => '15 May 2026',
        'semester' => 'Semester 2',
        'session' => '2025/2026'
    ]);
    exit();
}

$parts = explode('-', $period['session_semester']);
$session = $parts[0];
$semesterNum = $parts[1] ?? '2';
$semester = "Semester $semesterNum";

echo json_encode([
    'is_open' => (bool)$period['is_open'],
    'start_date' => date('d M Y', strtotime($period['start_date'])),
    'end_date' => date('d M Y', strtotime($period['end_date'])),
    'semester' => $semester,
    'session' => $session
]);
?>