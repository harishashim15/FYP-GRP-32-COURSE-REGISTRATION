<?php
ini_set('display_errors', 0);
error_reporting(0);

session_start();

header("Content-Type: text/plain; charset=utf-8");

$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "fypdb3";
$port = 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    echo "error";
    exit;
}

// GET INPUT
$loginCred = trim($_POST['username'] ?? '');
$password  = $_POST['password'] ?? '';

// VALIDATION
if (empty($loginCred) || empty($password)) {
    echo "invalid";
    exit;
}

// QUERY BY login_cred (VARCHAR column)
$sql  = "SELECT user_id, role, password FROM users WHERE login_cred = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "error";
    exit;
}

// Bind as string (s) because login_cred is VARCHAR
$stmt->bind_param("s", $loginCred);

$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    // PASSWORD CHECK (supports hashed or plain)
    if (password_verify($password, $row['password']) || $password === $row['password']) {

        $_SESSION['user_id']   = $row['user_id'];  // FIXED: column name is user_id
        $_SESSION['user_matrix'] = $loginCred;     // Store login_cred for reference
        $_SESSION['role']      = $row['role'];

        echo $row['role']; // admin / advisor / student

    } else {
        echo "invalid";
    }

} else {
    echo "invalid";
}

$stmt->close();
$conn->close();
?>