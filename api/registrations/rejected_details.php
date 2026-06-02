<?php
session_start();
header('Content-Type: application/json');

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
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// Get student info
$student_query = "SELECT u.user_name, u.matrix_number, u.utm_email, u.phone,
                         s.programme, s.year, s.ic_number, s.address
                  FROM users u
                  LEFT JOIN students s ON u.user_id = s.user_id
                  WHERE u.user_id = ?";
$stmt = $conn->prepare($student_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

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
    echo json_encode(['error' => 'Registration not found']);
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

echo json_encode([
    'student' => $student,
    'registration' => $registration,
    'courses' => $courses,
    'total_credits' => $total_credits
]);
?>