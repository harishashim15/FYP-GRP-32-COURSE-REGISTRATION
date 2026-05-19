<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "fypdb3";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

session_start();
?>