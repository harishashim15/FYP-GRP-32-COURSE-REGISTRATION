<?php
include("db.php");

if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = mysqli_query($conn,$query);

    if(mysqli_num_rows($result) > 0){
        $user = mysqli_fetch_assoc($result);

        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        if($user['role'] == "student"){
            header("Location: student_dashboard.php");
        }
    } else {
        echo "Login failed";
    }
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<form method="POST" class="container mt-5">
    <input type="text" name="email" placeholder="Email" class="form-control mb-2">
    <input type="password" name="password" placeholder="Password" class="form-control mb-2">
    <button name="login" class="btn btn-primary">Login</button>
</form>