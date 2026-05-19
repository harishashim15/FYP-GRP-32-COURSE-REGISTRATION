<?php
require_once __DIR__ . '/../config/database.php';

$advisor = requireRole('advisor');
$pdo = getDBConnection();

// Get advisor name from users table (fypdb3)
$stmt = $pdo->prepare("SELECT user_name FROM users WHERE user_id = ?");
$stmt->execute([$advisor['id']]);
$advisorName = $stmt->fetchColumn() ?: 'Advisor';

// Total students under this advisor (from students table)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE advisor_id = ?");
$stmt->execute([$advisor['id']]);
$totalStudents = (int)$stmt->fetchColumn();

// Pending approvals: registrations with status 'pending' for students under this advisor
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM course_registrations cr
    JOIN students s ON cr.student_id = s.user_id
    WHERE s.advisor_id = ? AND cr.status = 'pending'
");
$stmt->execute([$advisor['id']]);
$pendingApprovals = (int)$stmt->fetchColumn();

// Approved this week (last 7 days)
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM course_registrations cr
    JOIN students s ON cr.student_id = s.user_id
    WHERE s.advisor_id = ? AND cr.status = 'approved' AND cr.reviewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
");
$stmt->execute([$advisor['id']]);
$approvedThisWeek = (int)$stmt->fetchColumn();

echo json_encode([
    'advisorName' => $advisorName,
    'totalStudents' => $totalStudents,
    'pendingApprovals' => $pendingApprovals,
    'approvedThisWeek' => $approvedThisWeek
]);
?>