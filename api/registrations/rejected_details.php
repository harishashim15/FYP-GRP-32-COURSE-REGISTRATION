<?php
session_start();
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

// Use the existing config
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$registration_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$registration_id) {
    echo json_encode(['error' => 'No registration ID provided']);
    exit();
}

try {
    $pdo = getDBConnection();
} catch (Exception $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Check if registration exists and belongs to student
$stmt = $pdo->prepare("SELECT id, status, student_id FROM course_registrations WHERE id = ?");
$stmt->execute([$registration_id]);
$reg_check = $stmt->fetch();

if (!$reg_check) {
    echo json_encode(['error' => 'Registration ID not found']);
    exit();
}

if ($reg_check['student_id'] != $user_id) {
    echo json_encode(['error' => 'This registration does not belong to you']);
    exit();
}

if ($reg_check['status'] != 'rejected') {
    echo json_encode(['error' => 'Registration status is not rejected']);
    exit();
}

// Get student info from users table
$stmt = $pdo->prepare("SELECT user_id, user_name, matrix_number, utm_email, phone FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$student = $stmt->fetch();

// Get additional student info from students table
$stmt = $pdo->prepare("SELECT programme, year, ic_number, address FROM students WHERE user_id = ?");
$stmt->execute([$user_id]);
$student_details = $stmt->fetch();

if ($student_details) {
    $student = array_merge($student, $student_details);
}

// Get registration details
$stmt = $pdo->prepare("SELECT cr.id, cr.submission_date, cr.status, cr.session, cr.reviewed_at, 
                       cr.advisor_remarks, u.user_name as reviewed_by
                       FROM course_registrations cr
                       LEFT JOIN users u ON cr.reviewed_by = u.user_id
                       WHERE cr.id = ? AND cr.student_id = ?");
$stmt->execute([$registration_id, $user_id]);
$registration = $stmt->fetch();

// Get courses
$stmt = $pdo->prepare("SELECT rc.subject_code, s.subject_name, s.credits, rc.section
                       FROM registration_courses rc
                       JOIN subjects s ON rc.subject_code = s.subject_code
                       WHERE rc.registration_id = ?");
$stmt->execute([$registration_id]);
$courses = $stmt->fetchAll();

$total_credits = 0;
foreach ($courses as $course) {
    $total_credits += $course['credits'];
}

echo json_encode([
    'success' => true,
    'student' => $student,
    'registration' => $registration,
    'courses' => $courses,
    'total_credits' => $total_credits
]);
?>