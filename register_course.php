<?php
include("db.php");
session_start();

$user_id = $_SESSION['user_id'];
$course_id = $_GET['id'];

mysqli_query($conn,"INSERT INTO registrations (user_id,course_id,status) VALUES ('$user_id','$course_id','pending')");

echo "Registered!";
?>