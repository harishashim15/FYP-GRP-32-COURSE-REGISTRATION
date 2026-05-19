<?php
session_start();

$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "fypdb3";  // ← CHANGE THIS
$port = 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    echo "error";
    exit;
}

$loginCred = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($loginCred) || empty($password)) {
    echo "invalid";
    exit;
}

// Query by login_cred (since you added this column)
$sql = "SELECT user_id, role, password FROM users WHERE login_cred = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $loginCred);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password']) || $password === $row['password']) {
        $_SESSION['user_id'] = $row['user_id'];  // ← This is the key
        $_SESSION['role'] = $row['role'];
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