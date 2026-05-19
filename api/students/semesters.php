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

// Verify student belongs to advisor
$stmt = $pdo->prepare("SELECT 1 FROM students WHERE user_id = ? AND advisor_id = ?");
$stmt->execute([$studentId, $advisor['id']]);
if (!$stmt->fetch()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized to view this student']);
    exit;
}

// Fetch semester history
$stmt = $pdo->prepare("
    SELECT 
        session_semester AS session,
        programme,
        no_semester AS noSem,
        DATE_FORMAT(reg_date, '%d %b %Y') AS regDate,
        active_code AS activeCode,
        cpa
    FROM student_semesters
    WHERE student_id = ?
    ORDER BY no_semester ASC
");
$stmt->execute([$studentId]);
$history = $stmt->fetchAll();

// Format CPA as string with 2 decimals if not null
foreach ($history as &$row) {
    if ($row['cpa'] !== null) {
        $row['cpa'] = number_format((float)$row['cpa'], 2);
    } else {
        $row['cpa'] = '';
    }
}

echo json_encode($history);
?>