<?php
ini_set('display_errors', 0);
error_reporting(0);

session_start();

header("Content-Type: text/plain; charset=utf-8");

$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "dummyfyp";
$port = 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    echo "error";
    exit;
}

// GET INPUT
$id       = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// VALIDATION
if (empty($id) || empty($password)) {
    echo "invalid";
    exit;
}

// QUERY BY ID
$sql  = "SELECT id, role, password FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "error";
    exit;
}

// ⚠️ If your ID is INT, use "i" instead of "s"
$stmt->bind_param("i", $id);

$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    // PASSWORD CHECK (supports hashed or plain)
    if (password_verify($password, $row['password']) || $password === $row['password']) {

        $_SESSION['user_id'] = $row['id'];
        $_SESSION['role']    = $row['role'];

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