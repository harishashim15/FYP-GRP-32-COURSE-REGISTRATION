<?php
include("db.php");

$id = $_POST['id'];
$name = $_POST['name'];
$email = $_POST['email'];

mysqli_query($conn,"UPDATE users SET name='$name', email='$email' WHERE id='$id'");

header("Location: profile.php");
?>