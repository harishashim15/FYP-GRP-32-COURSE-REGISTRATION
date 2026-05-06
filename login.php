<?php
include("db.php");
session_start();

$email = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$query = "SELECT * FROM users WHERE email='$email' AND password='$password'";
$result = mysqli_query($conn,$query);

if(mysqli_num_rows($result) > 0){
    $user = mysqli_fetch_assoc($result);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];

    echo $user['role']; // return to JS
} else {
    echo "error";
}
?>