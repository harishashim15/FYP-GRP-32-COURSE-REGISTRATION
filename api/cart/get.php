<?php
require_once __DIR__ . '/../config/database.php';

$student = requireRole('student');
$pdo = getDBConnection();

$stmt = $pdo->prepare("
    SELECT c.subject_code as code, s.subject_name as name, s.credits, c.section
    FROM registration_cart c
    JOIN subjects s ON c.subject_code = s.subject_code
    WHERE c.student_id = ?
    ORDER BY c.added_date ASC
");
$stmt->execute([$student['id']]);
$items = $stmt->fetchAll();

$totalCredits = 0;
foreach ($items as $item) {
    $totalCredits += (int)$item['credits'];
}

echo json_encode([
    'items' => $items,
    'total_credits' => $totalCredits,
    'count' => count($items)
]);
?>