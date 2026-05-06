<?php
include("db.php");
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: index.html");
    exit();
}

$id = $_SESSION['user_id'];
$data = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE id='$id'"));
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-5">
    <h3>My Profile</h3>
    <p>Name: <?php echo $data['name']; ?></p>
    <p>Email: <?php echo $data['email']; ?></p>

    <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
    <a href="student_dashboard.php" class="btn btn-secondary">Back</a>
</div>