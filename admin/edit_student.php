<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

$admin_name = 'Admin';
$stmt = $conn->prepare("SELECT user_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $admin_name = $row['user_name'];
}
$stmt->close();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_students.php?msg=Invalid student ID.");
    exit();
}
$student_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT user_id, matrix_number, user_name, utm_email, phone FROM users WHERE user_id = ? AND role = 'student'");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: manage_students.php?msg=Student not found.");
    exit();
}
$student = $result->fetch_assoc();
$stmt->close();

$message = '';
$msg_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matrix = trim($_POST['matrix']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];

    $error = false;

    if (empty($matrix) || empty($name) || empty($email) || empty($phone)) {
        $message = "All fields are required.";
        $msg_type = 'danger';
        $error = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $msg_type = 'danger';
        $error = true;
    } else {
        // Check duplicate matrix (excluding current)
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE matrix_number = ? AND user_id != ?");
        $stmt->bind_param("si", $matrix, $student_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $message = "Matrix number already exists.";
            $msg_type = 'danger';
            $error = true;
        }
        $stmt->close();
        if (!$error) {
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE utm_email = ? AND user_id != ?");
            $stmt->bind_param("si", $email, $student_id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $message = "UTM email already exists.";
                $msg_type = 'danger';
                $error = true;
            }
            $stmt->close();
        }
    }

    if (!$error) {
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET matrix_number = ?, user_name = ?, utm_email = ?, phone = ?, password = ? WHERE user_id = ?");
            $stmt->bind_param("sssssi", $matrix, $name, $email, $phone, $hashed, $student_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET matrix_number = ?, user_name = ?, utm_email = ?, phone = ? WHERE user_id = ?");
            $stmt->bind_param("ssssi", $matrix, $name, $email, $phone, $student_id);
        }
        if ($stmt->execute()) {
            header("Location: manage_students.php?msg=Student updated successfully.");
            exit();
        } else {
            $message = "Database error: " . $conn->error;
            $msg_type = 'danger';
        }
        $stmt->close();
    }
}
?>
<!-- HTML similar to add_student.php but with values filled -->