<?php
include("db.php");
session_start();

if(isset($_POST['update'])){
    $id = $_SESSION['user_id'];
    $current = $_POST['current'];
    $new = $_POST['new'];

    $check = mysqli_query($conn,"SELECT * FROM users WHERE id='$id' AND password='$current'");

    if(mysqli_num_rows($check) > 0){
        mysqli_query($conn,"UPDATE users SET password='$new' WHERE id='$id'");
        echo "Password updated";
    } else {
        echo "Wrong current password";
    }
}
?>

<form method="POST" class="container mt-5">
    <input type="password" name="current" placeholder="Current Password" class="form-control mb-2">
    <input type="password" name="new" placeholder="New Password" class="form-control mb-2">
    <button name="update" class="btn btn-primary">Update</button>
</form>