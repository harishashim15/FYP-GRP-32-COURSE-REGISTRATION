<?php
include("db.php");
session_start();

$id = $_SESSION['user_id'];
$data = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE id='$id'"));
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<form action="update_profile.php" method="POST" class="container mt-5">
    <input type="hidden" name="id" value="<?php echo $data['id']; ?>">

    <input type="text" name="name" value="<?php echo $data['name']; ?>" class="form-control mb-2">
    <input type="text" name="email" value="<?php echo $data['email']; ?>" class="form-control mb-2">

    <button class="btn btn-success">Update</button>
</form>