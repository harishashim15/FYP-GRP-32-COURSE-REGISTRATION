<?php
/**
 * API: Get student dashboard statistics
 * Method: GET
 * Role: student
 * Response: JSON with stats
 */

require_once __DIR__ . '/../config/database.php';

// Only students can access this endpoint
$student = requireRole('student');

$pdo = getDBConnection();

// Get student's name
$studentName = $student['name'];

// 1. Get the student's current session and semester from student_semesters (latest)
$stmt = $pdo->prepare("
    SELECT session_semester, no_semester 
    FROM student_semesters 
    WHERE student_id = ? 
    ORDER BY no_semester DESC 
    LIMIT 1
");
$stmt->execute([$student['id']]);
$latest = $stmt->fetch();

$currentSession = $latest ? explode('-', $latest['session_semester'])[0] : '2025/2026';
$currentSemester = $latest ? $latest['no_semester'] : 2;

// 2. Get registered courses for the student (from course_registrations and registration_courses)
// Count total distinct courses registered (any status)
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT rc.subject_code) as total
    FROM course_registrations cr
    JOIN registration_courses rc ON cr.id = rc.registration_id
    WHERE cr.student_id = ?
");
$stmt->execute([$student['id']]);
$registeredCourses = $stmt->fetch()['total'];

// 3. Pending approvals (registrations with status 'pending')
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT rc.subject_code) as pending
    FROM course_registrations cr
    JOIN registration_courses rc ON cr.id = rc.registration_id
    WHERE cr.student_id = ? AND cr.status = 'pending'
");
$stmt->execute([$student['id']]);
$pendingApprovals = $stmt->fetch()['pending'];

// 4. Approved courses
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT rc.subject_code) as approved
    FROM course_registrations cr
    JOIN registration_courses rc ON cr.id = rc.registration_id
    WHERE cr.student_id = ? AND cr.status = 'approved'
");
$stmt->execute([$student['id']]);
$approvedCourses = $stmt->fetch()['approved'];

echo json_encode([
    'student_name' => $studentName,
    'registered_courses' => (int)$registeredCourses,
    'pending_approvals' => (int)$pendingApprovals,
    'approved_courses' => (int)$approvedCourses,
    'current_session' => $currentSession,
    'current_semester' => (int)$currentSemester
]);
?>