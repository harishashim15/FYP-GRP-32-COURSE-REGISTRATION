<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$registration_id = isset($_POST['registration_id']) ? (int)$_POST['registration_id'] : 0;

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

// Clear existing cart
$clear = "DELETE FROM registration_cart WHERE student_id = ?";
$stmt = $conn->prepare($clear);
$stmt->bind_param("i", $user_id);
$stmt->execute();

// Get courses from rejected registration
$courses_query = "SELECT subject_code, section 
                  FROM registration_courses 
                  WHERE registration_id = ?";
$stmt = $conn->prepare($courses_query);
$stmt->bind_param("i", $registration_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $insert = "INSERT INTO registration_cart (student_id, subject_code, section, added_date) 
               VALUES (?, ?, ?, NOW())";
    $insertStmt = $conn->prepare($insert);
    $insertStmt->bind_param("iss", $user_id, $row['subject_code'], $row['section']);
    $insertStmt->execute();
}

$conn->close();

echo json_encode(['success' => true, 'message' => 'Registration loaded for editing']);
?>