<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: index.html");
    exit();
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-5">
    <h2>Student Dashboard</h2>

    <a href="profile.php" class="btn btn-info mb-2">Profile</a><br>
    <a href="courses.php" class="btn btn-success mb-2">View Courses</a><br>
    <a href="my_registration.php" class="btn btn-warning mb-2">My Registration</a><br>
    <a href="change_password.php" class="btn btn-secondary mb-2">Change Password</a><br>
    <a href="logout.php" class="btn btn-danger">Logout</a>
</div>