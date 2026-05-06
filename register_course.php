<?php
include("db.php");
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$course_id = $_GET['id'];

mysqli_query($conn,"INSERT INTO registrations (user_id,course_id,status) VALUES ('$user_id','$course_id','pending')");

header("Location: my_registration.php");
?>