<?php
/**
 * API: Get advisor dashboard statistics
 * Method: GET
 * Role: advisor
 * Response: JSON with stats
 */

require_once __DIR__ . '/../config/database.php';

// Only advisors can access this endpoint
$advisor = requireRole('advisor');

$pdo = getDBConnection();

// Get advisor's name
$advisorName = $advisor['name'];

// 1. Total students under this advisor
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE advisor_id = ? AND role = 'student'");
$stmt->execute([$advisor['id']]);
$totalStudents = $stmt->fetch()['total'];

// 2. Pending approvals (registrations with status 'pending' for this advisor's students)
$stmt = $pdo->prepare("
    SELECT COUNT(*) as pending 
    FROM course_registrations cr
    JOIN users u ON cr.student_id = u.id
    WHERE u.advisor_id = ? AND cr.status = 'pending'
");
$stmt->execute([$advisor['id']]);
$pendingApprovals = $stmt->fetch()['pending'];

// 3. Approved this week (registrations approved in last 7 days)
$stmt = $pdo->prepare("
    SELECT COUNT(*) as approved_week 
    FROM course_registrations cr
    JOIN users u ON cr.student_id = u.id
    WHERE u.advisor_id = ? AND cr.status = 'approved' 
    AND cr.reviewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
");
$stmt->execute([$advisor['id']]);
$approvedThisWeek = $stmt->fetch()['approved_week'];

echo json_encode([
    'advisorName' => $advisorName,
    'totalStudents' => (int)$totalStudents,
    'pendingApprovals' => (int)$pendingApprovals,
    'approvedThisWeek' => (int)$approvedThisWeek
]);
?>