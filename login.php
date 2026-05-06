<?php
// Temporarily show all errors so we can see what's wrong
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = "sql207.infinityfree.com";
$user = "if0_41833955";
$pass = "PassFYP15";
$db   = "if0_41833955_fyp32_db";
$port = 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    echo "DB_ERROR: " . $conn->connect_error;
    exit;
}

$email    = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo "invalid";
    exit;
}

$sql  = "SELECT role, password FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "PREPARE_ERROR: " . $conn->error;
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Try password_verify first, fall back to plain-text check
    if (password_verify($password, $row['password'])) {
        echo $row['role'];
    } else if ($password === $row['password']) {
        // Plain-text match (old unhashed passwords)
        echo $row['role'];
    } else {
        echo "invalid";
    }
} else {
    echo "NO_USER_FOUND";
}

$stmt->close();
$conn->close();
?>