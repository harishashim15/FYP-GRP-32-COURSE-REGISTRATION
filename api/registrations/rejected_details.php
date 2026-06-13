<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

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

$host = "localhost";
$db   = "fypdb3";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// First, verify this registration belongs to the logged-in student and is rejected
$verify_query = "SELECT id, status FROM course_registrations WHERE id = ? AND student_id = ? AND status = 'rejected'";
$stmt = $conn->prepare($verify_query);
$stmt->bind_param("ii", $registration_id, $user_id);
$stmt->execute();
$verify_result = $stmt->get_result();

if ($verify_result->num_rows === 0) {
    echo json_encode(['error' => 'Registration not found or not rejected']);
    exit();
}

// Get student info from users table directly (simpler, more reliable)
$student_query = "SELECT user_id, user_name, matrix_number, utm_email, phone FROM users WHERE user_id = ?";
$stmt = $conn->prepare($student_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// Get additional student info from students table (if exists)
$student_details_query = "SELECT programme, year, ic_number, address FROM students WHERE user_id = ?";
$stmt = $conn->prepare($student_details_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student_details = $stmt->get_result()->fetch_assoc();

// Merge student data
if ($student_details) {
    $student = array_merge($student, $student_details);
}

// Get registration details with advisor remarks
$reg_query = "SELECT cr.id, cr.submission_date, cr.status, cr.session, cr.reviewed_at, 
                     cr.advisor_remarks, u.user_name as reviewed_by
              FROM course_registrations cr
              LEFT JOIN users u ON cr.reviewed_by = u.user_id
              WHERE cr.id = ? AND cr.student_id = ?";
$stmt = $conn->prepare($reg_query);
$stmt->bind_param("ii", $registration_id, $user_id);
$stmt->execute();
$registration = $stmt->get_result()->fetch_assoc();

if (!$registration) {
    echo json_encode(['error' => 'Registration details not found']);
    exit();
}

// Get registered courses
$courses_query = "SELECT rc.subject_code, s.subject_name, s.credits, rc.section
                  FROM registration_courses rc
                  JOIN subjects s ON rc.subject_code = s.subject_code
                  WHERE rc.registration_id = ?";
$stmt = $conn->prepare($courses_query);
$stmt->bind_param("i", $registration_id);
$stmt->execute();
$courses_result = $stmt->get_result();

$courses = [];
$total_credits = 0;
while ($row = $courses_result->fetch_assoc()) {
    $courses[] = $row;
    $total_credits += $row['credits'];
}

$conn->close();

// Return success response
echo json_encode([
    'success' => true,
    'student' => $student,
    'registration' => $registration,
    'courses' => $courses,
    'total_credits' => $total_credits
]);
?>