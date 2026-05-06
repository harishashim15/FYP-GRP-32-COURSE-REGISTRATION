<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Step 1: PHP is working<br>";

$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "dummyfyp";
$port = 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    echo "Step 2: DB CONNECTION FAILED - " . $conn->connect_error;
    exit;
}

echo "Step 2: DB connected successfully<br>";

$result = $conn->query("SELECT id, name, email, role FROM users");

if (!$result) {
    echo "Step 3: Query failed - " . $conn->error;
    exit;
}

echo "Step 3: Query worked. Users found:<br><br>";
while ($row = $result->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | Name: " . $row['name'] . " | Email: " . $row['email'] . " | Role: " . $row['role'] . "<br>";
}

$conn->close();
echo "<br>All done — everything is working!";
?>
