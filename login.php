<?php
$host = "sql207.infinityfree.com";
$user = "if0_41833955";
$pass = "PassFYP15";
$db   = "if0_41833955_XXX";
$port = 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$name = $_POST['username']; // keep this if your input field is still "username"
$password = $_POST['password'];

$sql = "SELECT role FROM users WHERE name=? AND password=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $name, $password);
$stmt->execute();

$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo $row['role'];
} else {
    echo "invalid";
}

$conn->close();
?>