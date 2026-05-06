<?php
ini_set('display_errors', 0);
error_reporting(0);

session_start(); // ADD THIS

header("Content-Type: text/plain; charset=utf-8");

$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "dummyfyp";  // keep as is
$port = 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    echo "error";
    exit;
}

$email    = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo "invalid";
    exit;
}

$sql  = "SELECT id, role, password FROM users WHERE email = ?"; // added id
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "error";
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password']) || $password === $row['password']) {
        $_SESSION['user_id'] = $row['id'];   // ADD THIS
        $_SESSION['role']    = $row['role']; // ADD THIS
        echo $row['role'];
    } else {
        echo "invalid";
    }
} else {
    echo "invalid";
}

$stmt->close();
$conn->close();
?>