<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create a response array to track what's happening
$debug = [];

$debug['step1'] = 'Script started';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in', 'debug' => $debug]);
    exit();
}

$debug['user_id'] = $_SESSION['user_id'];
$user_id = $_SESSION['user_id'];
$registration_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$debug['registration_id'] = $registration_id;

if (!$registration_id) {
    echo json_encode(['error' => 'No registration ID provided', 'debug' => $debug]);
    exit();
}

$host = "localhost";
$db   = "fypdb3";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error, 'debug' => $debug]);
    exit();
}

$debug['step2'] = 'Database connected';

// First, check if registration exists and belongs to student
$verify_query = "SELECT id, status, student_id FROM course_registrations WHERE id = ?";
$stmt = $conn->prepare($verify_query);
$stmt->bind_param("i", $registration_id);
$stmt->execute();
$verify_result = $stmt->get_result();
$reg_check = $verify_result->fetch_assoc();

$debug['reg_check'] = $reg_check;

if (!$reg_check) {
    echo json_encode(['error' => 'Registration ID not found in database', 'debug' => $debug]);
    exit();
}

if ($reg_check['student_id'] != $user_id) {
    echo json_encode(['error' => 'This registration does not belong to you', 'debug' => $debug]);
    exit();
}

if ($reg_check['status'] != 'rejected') {
    echo json_encode(['error' => 'Registration status is not rejected (it is: ' . $reg_check['status'] . ')', 'debug' => $debug]);
    exit();
}

$debug['step3'] = 'Registration verified';

// Get student info from users table
$student_query = "SELECT user_id, user_name, matrix_number, utm_email, phone FROM users WHERE user_id = ?";
$stmt = $conn->prepare($student_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

$debug['student_from_users'] = $student ? 'Found' : 'Not found';

// Get additional student info from students table
$student_details_query = "SELECT programme, year, ic_number, address FROM students WHERE user_id = ?";
$stmt = $conn->prepare($student_details_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student_details = $stmt->get_result()->fetch_assoc();

$debug['student_from_details'] = $student_details ? 'Found' : 'Not found';

if ($student_details) {
    $student = array_merge($student, $student_details);
}

// Get registration details
$reg_query = "SELECT cr.id, cr.submission_date, cr.status, cr.session, cr.reviewed_at, 
                     cr.advisor_remarks, u.user_name as reviewed_by
              FROM course_registrations cr
              LEFT JOIN users u ON cr.reviewed_by = u.user_id
              WHERE cr.id = ? AND cr.student_id = ?";
$stmt = $conn->prepare($reg_query);
$stmt->bind_param("ii", $registration_id, $user_id);
$stmt->execute();
$registration = $stmt->get_result()->fetch_assoc();

$debug['registration_found'] = $registration ? 'Yes' : 'No';

// Get courses
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

$debug['courses_count'] = count($courses);
$debug['total_credits'] = $total_credits;

$conn->close();

$debug['step4'] = 'Success - returning data';

echo json_encode([
    'success' => true,
    'student' => $student,
    'registration' => $registration,
    'courses' => $courses,
    'total_credits' => $total_credits,
    'debug' => $debug
]);
?>