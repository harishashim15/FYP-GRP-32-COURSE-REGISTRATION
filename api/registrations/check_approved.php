<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['has_approved' => false, 'error' => 'Not authenticated']);
    exit();
}

require_once __DIR__ . '/../db_connect.php';

$user_id = $_SESSION['user_id'];
$current_session = "2025/2026 - Semester 2";

// Check if student has an approved registration for current session
$query = "SELECT COUNT(*) as count FROM course_registrations 
          WHERE student_id = '$user_id' 
          AND status = 'approved' 
          AND session = '$current_session'";

$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

$has_approved = ($row['count'] > 0);

echo json_encode(['has_approved' => $has_approved]);
?>