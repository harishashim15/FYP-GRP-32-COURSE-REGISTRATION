<?php
/**
 * API: Get course registration status for current student
 * Returns status for all courses (null if never registered)
 */

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

$host = "localhost";
$db   = "fypdb3";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// First get all available courses
$all_courses_sql = "SELECT subject_code FROM subjects";
$all_courses_result = $conn->query($all_courses_sql);
$all_courses = [];
while ($row = $all_courses_result->fetch_assoc()) {
    $all_courses[$row['subject_code']] = null; // Default status = null (never registered)
}

// Then get registered courses with status
$registered_sql = "SELECT DISTINCT 
                        rc.subject_code,
                        cr.status
                    FROM registration_courses rc
                    JOIN course_registrations cr ON rc.registration_id = cr.id
                    WHERE cr.student_id = ?";
$stmt = $conn->prepare($registered_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $all_courses[$row['subject_code']] = $row['status'];
}

$stmt->close();
$conn->close();

// Convert to array format
$courses = [];
foreach ($all_courses as $code => $status) {
    $courses[] = [
        'code' => $code,
        'status' => $status
    ];
}

echo json_encode(['courses' => $courses]);
?>