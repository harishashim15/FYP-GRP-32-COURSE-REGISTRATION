<?php
/**
 * API: Get current registration period
 * Method: GET
 * Role: any authenticated user (advisor or student)
 * Response: JSON with period details
 */

require_once __DIR__ . '/../config/database.php';

// Require authentication (any role)
$user = requireAuth();

$pdo = getDBConnection();

// Get the latest active or upcoming registration period
// Prioritize open period, otherwise show the most recent one
$stmt = $pdo->prepare("
    SELECT 
        session_semester,
        start_date,
        end_date,
        is_open
    FROM semester_registration_periods
    ORDER BY is_open DESC, start_date DESC
    LIMIT 1
");
$stmt->execute();
$period = $stmt->fetch();

if (!$period) {
    // Fallback default values if no period configured
    echo json_encode([
        'is_open' => true,
        'start_date' => '1 May 2026',
        'end_date' => '15 May 2026',
        'semester' => 'Semester 2',
        'session' => '2025/2026'
    ]);
    exit();
}

// Parse session_semester format, e.g., "2025/2026-2"
$parts = explode('-', $period['session_semester']);
$session = $parts[0]; // "2025/2026"
$semesterNum = isset($parts[1]) ? $parts[1] : '2';
$semester = "Semester " . $semesterNum;

// Format dates for display
$startDate = date('d M Y', strtotime($period['start_date']));
$endDate = date('d M Y', strtotime($period['end_date']));

echo json_encode([
    'is_open' => (bool)$period['is_open'],
    'start_date' => $startDate,
    'end_date' => $endDate,
    'semester' => $semester,
    'session' => $session
]);
?>