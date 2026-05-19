<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "fypdb3";  // or "dummyfyp" - whichever you're using

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>