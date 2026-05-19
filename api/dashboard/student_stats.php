<?php
/**
 * API: Get student dashboard statistics for fypdb3
 */

require_once __DIR__ . '/../config/database.php';

// Debug: Log session info
error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'not set'));

$student = requireRole('student');
$pdo = getDBConnection();

$studentId = $student['id'];
$studentName = $student['name'] ?? 'Student';

error_log("Student ID: $studentId, Name: $studentName");

// Get registered courses count
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT rc.subject_code) as total
    FROM course_registrations cr
    JOIN registration_courses rc ON cr.id = rc.registration_id
    WHERE cr.student_id = ?
");
$stmt->execute([$studentId]);
$registeredCourses = $stmt->fetch()['total'] ?? 0;

// Pending approvals
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT rc.subject_code) as pending
    FROM course_registrations cr
    JOIN registration_courses rc ON cr.id = rc.registration_id
    WHERE cr.student_id = ? AND cr.status = 'pending'
");
$stmt->execute([$studentId]);
$pendingApprovals = $stmt->fetch()['pending'] ?? 0;

// Approved courses
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT rc.subject_code) as approved
    FROM course_registrations cr
    JOIN registration_courses rc ON cr.id = rc.registration_id
    WHERE cr.student_id = ? AND cr.status = 'approved'
");
$stmt->execute([$studentId]);
$approvedCourses = $stmt->fetch()['approved'] ?? 0;

// Get current session
$stmt = $pdo->prepare("
    SELECT session_semester, no_semester 
    FROM student_semesters 
    WHERE student_id = ? 
    ORDER BY no_semester DESC 
    LIMIT 1
");
$stmt->execute([$studentId]);
$latest = $stmt->fetch();

$currentSession = $latest ? explode('-', $latest['session_semester'])[0] : '2025/2026';
$currentSemester = $latest ? $latest['no_semester'] : 2;

// Return JSON
$response = [
    'student_name' => $studentName,
    'registered_courses' => (int)$registeredCourses,
    'pending_approvals' => (int)$pendingApprovals,
    'approved_courses' => (int)$approvedCourses,
    'current_session' => $currentSession,
    'current_semester' => (int)$currentSemester
];

// Debug: Log the response
error_log("Response: " . json_encode($response));

echo json_encode($response);
?>