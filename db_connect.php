<?php
// db_connect.php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "dummyfyp";
$port = 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>